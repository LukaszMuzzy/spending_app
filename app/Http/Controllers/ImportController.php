<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Shop;
use App\Models\ShoppingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ImportController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Imports/Index', [
            'shopping_groups' => $user->shopping_groups()->orderBy('name')->get(),
            'payment_methods' => $user->payment_methods()->orderBy('name')->get(),
            'shops' => Shop::orderBy('name')->get(),
            'shopping_types' => ShoppingType::orderBy('description')->get(),
            'preview' => session('import_preview'),
            'mapping' => session('import_mapping'),
            'defaults' => session('import_defaults'),
        ]);
    }

    public function preview(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'date_column' => 'required|string',
            'amount_column' => 'required|string',
            'description_column' => 'required|string',
            'shopping_group_id' => 'required|exists:shopping_groups,id',
            'shop_id' => 'required|exists:shops,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'shopping_type_id' => 'required|exists:shopping_types,id',
            'has_header' => 'boolean',
        ]);

        $this->assertGroupAccess((int) $validated['shopping_group_id']);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return back()->withErrors(['file' => 'Could not read the uploaded file.']);
        }

        $hasHeader = $request->boolean('has_header', true);
        $headers = [];
        $rows = [];

        if ($hasHeader) {
            $headers = $this->normalizeRow(fgetcsv($handle) ?: []);
        }

        $rowIndex = 0;
        while (($raw = fgetcsv($handle)) !== false && count($rows) < 25) {
            $row = $this->normalizeRow($raw);
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            if (! $hasHeader && empty($headers)) {
                $headers = array_map(fn ($i) => 'Column '.($i + 1), array_keys($row));
            }

            $mapped = $this->mapRow($row, $headers, $validated);
            if ($mapped === null) {
                continue;
            }

            $rows[] = $mapped;
            $rowIndex++;
        }

        fclose($handle);

        // Re-read full file into session for confirm step (cap at 2000 rows)
        $request->session()->put('import_file_contents', file_get_contents($path));
        $request->session()->put('import_preview', $rows);
        $request->session()->put('import_mapping', [
            'date_column' => $validated['date_column'],
            'amount_column' => $validated['amount_column'],
            'description_column' => $validated['description_column'],
            'has_header' => $hasHeader,
            'headers' => $headers,
        ]);
        $request->session()->put('import_defaults', [
            'shopping_group_id' => (int) $validated['shopping_group_id'],
            'shop_id' => (int) $validated['shop_id'],
            'payment_method_id' => (int) $validated['payment_method_id'],
            'shopping_type_id' => (int) $validated['shopping_type_id'],
        ]);

        return redirect()->route('imports.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $contents = $request->session()->get('import_file_contents');
        $mapping = $request->session()->get('import_mapping');
        $defaults = $request->session()->get('import_defaults');

        if (! $contents || ! $mapping || ! $defaults) {
            return redirect()->route('imports.index')->withErrors([
                'file' => 'Upload and preview a CSV before importing.',
            ]);
        }

        $this->assertGroupAccess((int) $defaults['shopping_group_id']);

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $contents);
        rewind($handle);

        $headers = $mapping['headers'] ?? [];
        if ($mapping['has_header']) {
            fgetcsv($handle);
        }

        $imported = 0;
        $skipped = 0;

        while (($raw = fgetcsv($handle)) !== false) {
            $row = $this->normalizeRow($raw);
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $mapped = $this->mapRow($row, $headers, $mapping);
            if ($mapped === null) {
                $skipped++;
                continue;
            }

            $exists = Receipt::query()
                ->where('shopping_group_id', $defaults['shopping_group_id'])
                ->whereDate('date', $mapped['date'])
                ->where('price', $mapped['amount'])
                ->where('note', $mapped['description'])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $request->user()->receipts()->create([
                'date' => $mapped['date'],
                'price' => $mapped['amount'],
                'note' => $mapped['description'],
                'shopping_group_id' => $defaults['shopping_group_id'],
                'shop_id' => $defaults['shop_id'],
                'payment_method_id' => $defaults['payment_method_id'],
                'shopping_type_id' => $defaults['shopping_type_id'],
                'source' => 'csv',
                'needs_review' => false,
            ]);

            $imported++;

            if ($imported + $skipped >= 2000) {
                break;
            }
        }

        fclose($handle);

        $request->session()->forget(['import_file_contents', 'import_preview', 'import_mapping', 'import_defaults']);

        return redirect()
            ->route('receipts.index')
            ->with('flash', [
                'message' => "Imported {$imported} rows, skipped {$skipped}.",
            ]);
    }

    private function mapRow(array $row, array $headers, array $mapping): ?array
    {
        $dateValue = $this->cell($row, $headers, $mapping['date_column']);
        $amountValue = $this->cell($row, $headers, $mapping['amount_column']);
        $description = trim((string) $this->cell($row, $headers, $mapping['description_column']));

        $amount = $this->parseAmount($amountValue);
        $date = $this->parseDate($dateValue);

        if ($amount === null || $date === null) {
            return null;
        }

        return [
            'date' => $date,
            'amount' => $amount,
            'description' => $description !== '' ? mb_substr($description, 0, 255) : 'Imported',
        ];
    }

    private function cell(array $row, array $headers, string $column)
    {
        if (is_numeric($column)) {
            return $row[(int) $column] ?? null;
        }

        $index = array_search($column, $headers, true);
        if ($index === false) {
            return null;
        }

        return $row[$index] ?? null;
    }

    private function parseAmount($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $raw = trim((string) $value);
        $negative = false;

        if (preg_match('/^\((.*)\)$/', $raw, $matches)) {
            $raw = $matches[1];
            $negative = true;
        }

        $raw = str_replace([',', '£', '$', ' '], '', $raw);
        if ($raw === '' || ! is_numeric($raw)) {
            return null;
        }

        $amount = abs((float) $raw);
        if ($negative || str_starts_with(trim((string) $value), '-')) {
            // Treat as spend either way; store absolute.
        }

        return round($amount, 2);
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeRow(array $row): array
    {
        return array_map(fn ($cell) => is_string($cell) ? trim($cell) : $cell, $row);
    }

    private function rowIsEmpty(array $row): bool
    {
        return collect($row)->filter(fn ($cell) => $cell !== null && $cell !== '')->isEmpty();
    }

    private function assertGroupAccess(int $groupId): void
    {
        abort_unless(in_array($groupId, Auth::user()->shoppingGroupIds(), true), 403);
    }
}

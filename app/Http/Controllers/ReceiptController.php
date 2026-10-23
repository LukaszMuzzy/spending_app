<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\ShoppingType;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ReceiptController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $groupIds = $user->shoppingGroupIds();
        $shoppingGroups = $user->shopping_groups()->orderBy('name')->get();
        $paymentMethods = $user->payment_methods()->orderBy('name')->get();

        $query = Receipt::query()
            ->with(['user', 'shopping_type', 'payment_method', 'shop', 'shopping_group'])
            ->whereIn('shopping_group_id', $groupIds ?: [0])
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                    ->orWhereHas('shop', fn ($shop) => $shop->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('shopping_type', fn ($type) => $type->where('description', 'like', "%{$search}%"));
            });
        }

        if ($month = $request->string('month')->toString()) {
            try {
                $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $end = $start->copy()->endOfMonth();
                $query->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            } catch (\Throwable) {
                // ignore invalid month filter
            }
        }

        if ($request->filled('shopping_type_id')) {
            $query->where('shopping_type_id', $request->integer('shopping_type_id'));
        }

        if ($request->filled('shopping_group_id')) {
            $query->where('shopping_group_id', $request->integer('shopping_group_id'));
        }

        if ($request->boolean('needs_review')) {
            $query->where('needs_review', true);
        }

        return Inertia::render('Shopping/Receipts/Index', [
            'receipts' => $query->paginate(25)->withQueryString(),
            'filters' => [
                'search' => $request->string('search')->toString(),
                'month' => $request->string('month')->toString() ?: now()->format('Y-m'),
                'shopping_type_id' => $request->input('shopping_type_id', ''),
                'shopping_group_id' => $request->input('shopping_group_id', ''),
                'needs_review' => $request->boolean('needs_review'),
            ],
            'shopping_types' => ShoppingType::orderBy('description')->get(),
            'payment_methods' => $paymentMethods,
            'shops' => Shop::orderBy('name')->get(),
            'shopping_groups' => $shoppingGroups,
            'inbox_count' => Receipt::query()
                ->whereIn('shopping_group_id', $groupIds ?: [0])
                ->where('needs_review', true)
                ->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $this->assertGroupAccess((int) $validated['shopping_group_id']);

        $request->user()->receipts()->create([
            ...$validated,
            'source' => 'manual',
            'needs_review' => false,
        ]);

        return redirect()->route('receipts.index');
    }

    public function update(Request $request, Receipt $receipt): RedirectResponse
    {
        $this->assertGroupAccess((int) $receipt->shopping_group_id);

        $validated = $this->validated($request);
        $this->assertGroupAccess((int) $validated['shopping_group_id']);

        $receipt->update([
            ...$validated,
            'needs_review' => false,
        ]);

        return redirect()->back();
    }

    public function destroy(Receipt $receipt): RedirectResponse
    {
        $this->assertGroupAccess((int) $receipt->shopping_group_id);
        $receipt->delete();

        return redirect()->back();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'shopping_type_id' => 'required|numeric|exists:shopping_types,id',
            'payment_method_id' => 'required|numeric|exists:payment_methods,id',
            'shop_id' => 'required|numeric|exists:shops,id',
            'price' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
            'date' => 'required|date',
            'shopping_group_id' => 'required|numeric|exists:shopping_groups,id',
        ]);
    }

    private function assertGroupAccess(int $groupId): void
    {
        abort_unless(in_array($groupId, Auth::user()->shoppingGroupIds(), true), 403);
    }
}

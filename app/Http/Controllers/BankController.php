<?php

namespace App\Http\Controllers;

use App\Models\BankConnection;
use App\Models\Receipt;
use App\Models\Shop;
use App\Models\ShoppingType;
use App\Services\EnableBankingClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class BankController extends Controller
{
    public function index(Request $request, EnableBankingClient $client): Response
    {
        $user = $request->user();
        $connection = BankConnection::query()
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        return Inertia::render('Bank/Index', [
            'configured' => $client->isConfigured(),
            'connection' => $connection,
            'shopping_groups' => $user->shopping_groups()->orderBy('name')->get(),
            'payment_methods' => $user->payment_methods()->orderBy('name')->get(),
            'shops' => Shop::orderBy('name')->get(),
            'shopping_types' => ShoppingType::orderBy('description')->get(),
            'inbox_count' => Receipt::query()
                ->whereIn('shopping_group_id', $user->shoppingGroupIds() ?: [0])
                ->where('needs_review', true)
                ->where('source', 'bank')
                ->count(),
        ]);
    }

    public function connect(Request $request, EnableBankingClient $client): RedirectResponse
    {
        if (! $client->isConfigured()) {
            return back()->withErrors([
                'bank' => 'Add ENABLEBANKING_APP_ID and ENABLEBANKING_PRIVATE_KEY to enable bank sync.',
            ]);
        }

        try {
            $auth = $client->createAuthorizationUrl(route('bank.callback'));
        } catch (RuntimeException $e) {
            Log::warning('Enable Banking connect failed', ['message' => $e->getMessage()]);

            return back()->withErrors(['bank' => 'Could not start bank connection. Check your Enable Banking credentials.']);
        }

        $request->session()->put('enablebanking_state', $auth['state'] ?? null);

        BankConnection::updateOrCreate(
            ['user_id' => $request->user()->id, 'provider' => 'enable_banking'],
            [
                'status' => 'pending',
                'institution_name' => $auth['aspsp']['name'] ?? 'Bank',
            ]
        );

        return redirect()->away($auth['url'] ?? $auth['authorization_url'] ?? route('bank.index'));
    }

    public function callback(Request $request, EnableBankingClient $client): RedirectResponse
    {
        if (! $client->isConfigured()) {
            return redirect()->route('bank.index');
        }

        $code = $request->string('code')->toString();
        if ($code === '') {
            return redirect()->route('bank.index')->withErrors([
                'bank' => 'Bank connection was cancelled or incomplete.',
            ]);
        }

        try {
            $session = $client->createSession($code);
        } catch (RuntimeException $e) {
            Log::warning('Enable Banking callback failed', ['message' => $e->getMessage()]);

            return redirect()->route('bank.index')->withErrors([
                'bank' => 'Could not finish bank connection.',
            ]);
        }

        $accounts = $session['accounts'] ?? [];
        $balance = null;

        if (! empty($accounts[0]['uid'] ?? null)) {
            try {
                $balances = $client->getAccountBalances($accounts[0]['uid']);
                $balance = $this->extractBalance($balances);
            } catch (RuntimeException) {
                $balance = null;
            }
        }

        BankConnection::updateOrCreate(
            ['user_id' => $request->user()->id, 'provider' => 'enable_banking'],
            [
                'status' => 'connected',
                'session_id' => $session['session_id'] ?? ($session['id'] ?? null),
                'accounts' => $accounts,
                'institution_name' => $session['aspsp']['name'] ?? 'Connected bank',
                'expires_at' => now()->addDays(90),
                'balance' => $balance,
                'last_synced_at' => null,
            ]
        );

        return redirect()->route('bank.index');
    }

    public function sync(Request $request, EnableBankingClient $client): RedirectResponse
    {
        $validated = $request->validate([
            'shopping_group_id' => 'required|exists:shopping_groups,id',
            'shop_id' => 'required|exists:shops,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'shopping_type_id' => 'required|exists:shopping_types,id',
        ]);

        abort_unless(in_array((int) $validated['shopping_group_id'], Auth::user()->shoppingGroupIds(), true), 403);

        $connection = BankConnection::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'connected')
            ->latest()
            ->first();

        if (! $connection || ! $client->isConfigured()) {
            return back()->withErrors(['bank' => 'Connect a bank account before syncing.']);
        }

        $uncategorised = ShoppingType::firstOrCreate(
            ['description' => 'Uncategorised'],
            ['description' => 'Uncategorised']
        );

        $imported = 0;
        $accounts = $connection->accounts ?? [];

        foreach ($accounts as $account) {
            $uid = $account['uid'] ?? $account['account_id'] ?? null;
            if (! $uid) {
                continue;
            }

            try {
                $payload = $client->getAccountTransactions($uid, now()->subDays(90)->toDateString());
                $balances = $client->getAccountBalances($uid);
                $connection->balance = $this->extractBalance($balances) ?? $connection->balance;
            } catch (RuntimeException $e) {
                Log::warning('Enable Banking sync failed', ['message' => $e->getMessage()]);
                continue;
            }

            foreach ($payload['transactions'] ?? [] as $tx) {
                $status = strtolower((string) ($tx['status'] ?? 'BOOK'));
                if ($status === 'pend' || $status === 'pending') {
                    continue;
                }

                $externalId = (string) ($tx['entry_reference'] ?? $tx['transaction_id'] ?? $tx['id'] ?? '');
                if ($externalId === '') {
                    continue;
                }

                $amount = abs((float) ($tx['transaction_amount']['amount'] ?? $tx['amount'] ?? 0));
                if ($amount <= 0) {
                    continue;
                }

                $creditDebit = strtoupper((string) ($tx['credit_debit_indicator'] ?? 'DBIT'));
                if ($creditDebit === 'CRDT') {
                    continue;
                }

                $date = $tx['booking_date'] ?? $tx['value_date'] ?? null;
                if (! $date) {
                    continue;
                }

                $note = $tx['remittance_information'][0]
                    ?? $tx['creditor']['name']
                    ?? $tx['debtor']['name']
                    ?? 'Bank transaction';

                $exists = Receipt::query()
                    ->where('source', 'bank')
                    ->where('external_id', $externalId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $request->user()->receipts()->create([
                    'date' => $date,
                    'price' => $amount,
                    'note' => mb_substr((string) $note, 0, 255),
                    'shopping_group_id' => $validated['shopping_group_id'],
                    'shop_id' => $validated['shop_id'],
                    'payment_method_id' => $validated['payment_method_id'],
                    'shopping_type_id' => $uncategorised->id,
                    'source' => 'bank',
                    'external_id' => $externalId,
                    'needs_review' => true,
                ]);

                $imported++;
            }
        }

        $connection->update([
            'last_synced_at' => now(),
            'balance' => $connection->balance,
        ]);

        return redirect()
            ->route('receipts.index', ['needs_review' => 1])
            ->with('flash', ['message' => "Synced {$imported} bank transactions into your inbox."]);
    }

    public function disconnect(Request $request): RedirectResponse
    {
        BankConnection::query()
            ->where('user_id', $request->user()->id)
            ->delete();

        return redirect()->route('bank.index');
    }

    private function extractBalance(array $payload): ?float
    {
        $balances = $payload['balances'] ?? $payload;
        if (! is_array($balances)) {
            return null;
        }

        foreach ($balances as $balance) {
            if (! is_array($balance)) {
                continue;
            }
            $amount = $balance['balance_amount']['amount'] ?? $balance['amount'] ?? null;
            if ($amount !== null) {
                return (float) $amount;
            }
        }

        return null;
    }
}

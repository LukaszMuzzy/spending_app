<?php

namespace App\Http\Controllers;

use App\Models\BankConnection;
use App\Services\SpendingProjection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, SpendingProjection $projection): Response
    {
        $user = $request->user();
        $groupIds = $user->shoppingGroupIds();

        $data = empty($groupIds)
            ? [
                'month_label' => now()->format('F Y'),
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->endOfMonth()->toDateString(),
                'spent_so_far' => 0,
                'budget_total' => 0,
                'expected_income' => 0,
                'projected_spend' => 0,
                'remaining_bills_total' => 0,
                'variable_estimate' => 0,
                'money_left' => 0,
                'budget_remaining' => 0,
                'pace_tone' => 'default',
                'category_breakdown' => [],
                'monthly_history' => [],
                'top_shops' => [],
                'upcoming_bills' => [],
                'remaining_bills' => [],
            ]
            : $projection->forUser($user->id, $groupIds);

        $bank = BankConnection::query()
            ->where('user_id', $user->id)
            ->where('status', 'connected')
            ->latest()
            ->first();

        return Inertia::render('Dashboard', [
            ...$data,
            'bank_balance' => $bank?->balance,
            'bank_institution' => $bank?->institution_name,
            'bank_last_synced_at' => $bank?->last_synced_at?->toDateTimeString(),
        ]);
    }
}

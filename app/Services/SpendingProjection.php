<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\DdPayment;
use App\Models\Income;
use App\Models\Receipt;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SpendingProjection
{
    public function forUser(int $userId, array $groupIds, ?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? Carbon::now())->copy()->startOfDay();
        $monthStart = $asOf->copy()->startOfMonth();
        $monthEnd = $asOf->copy()->endOfMonth();
        $daysLeft = max(0, $asOf->diffInDays($monthEnd));
        $daysElapsed = max(1, $monthStart->diffInDays($asOf) + 1);

        $spentSoFar = (float) Receipt::query()
            ->whereIn('shopping_group_id', $groupIds)
            ->whereBetween('date', [$monthStart->toDateString(), $asOf->toDateString()])
            ->sum('price');

        $budgetTotal = (float) Budget::query()
            ->whereIn('shopping_group_id', $groupIds)
            ->sum('amount');

        $expectedIncome = $this->expectedIncomeThisMonth($groupIds, $monthStart, $monthEnd);
        $remainingBills = $this->remainingBills($groupIds, $asOf, $monthEnd);
        $remainingBillsTotal = (float) $remainingBills->sum('amount');

        $variableDaily = $this->variableDailyAverage($groupIds, $asOf);
        $variableEstimate = round($variableDaily * $daysLeft, 2);
        $projectedSpend = round($spentSoFar + $remainingBillsTotal + $variableEstimate, 2);

        $categoryBreakdown = $this->categoryBreakdown($groupIds, $monthStart, $asOf);
        $monthlyHistory = $this->monthlyHistory($groupIds, $asOf, 6);
        $topShops = $this->topShops($groupIds, $monthStart, $asOf);
        $upcomingBills = $this->upcomingBills($groupIds, $asOf, 90);

        $paceTone = 'default';
        if ($budgetTotal > 0) {
            $expectedPace = ($budgetTotal / max(1, $monthStart->daysInMonth)) * $daysElapsed;
            $paceTone = $spentSoFar > $expectedPace ? 'warn' : 'good';
        }

        return [
            'month_label' => $asOf->format('F Y'),
            'start_date' => $monthStart->toDateString(),
            'end_date' => $monthEnd->toDateString(),
            'spent_so_far' => $spentSoFar,
            'budget_total' => $budgetTotal,
            'expected_income' => $expectedIncome,
            'projected_spend' => $projectedSpend,
            'remaining_bills_total' => $remainingBillsTotal,
            'variable_estimate' => $variableEstimate,
            'money_left' => round($expectedIncome - $projectedSpend, 2),
            'budget_remaining' => round($budgetTotal - $projectedSpend, 2),
            'pace_tone' => $paceTone,
            'category_breakdown' => $categoryBreakdown,
            'monthly_history' => $monthlyHistory,
            'top_shops' => $topShops,
            'upcoming_bills' => $upcomingBills,
            'remaining_bills' => $remainingBills->values(),
        ];
    }

    private function expectedIncomeThisMonth(array $groupIds, Carbon $monthStart, Carbon $monthEnd): float
    {
        $incomes = Income::query()
            ->whereIn('shopping_group_id', $groupIds)
            ->where('is_active', true)
            ->get();

        $total = 0.0;

        foreach ($incomes as $income) {
            $total += $this->occurrencesInRange(
                Carbon::parse($income->next_date),
                $income->frequency,
                $monthStart,
                $monthEnd
            ) * (float) $income->amount;
        }

        return round($total, 2);
    }

    private function remainingBills(array $groupIds, Carbon $from, Carbon $monthEnd): Collection
    {
        $payments = DdPayment::query()
            ->with(['shoppingType', 'shop', 'shoppingGroup'])
            ->whereIn('shopping_group_id', $groupIds)
            ->where('is_active', true)
            ->get();

        $items = collect();

        foreach ($payments as $payment) {
            $dates = $this->scheduleDates(
                Carbon::parse($payment->payment_date),
                $payment->frequency ?: 'monthly',
                $from->copy()->addDay(),
                $monthEnd,
                $payment->end_date ? Carbon::parse($payment->end_date) : null
            );

            foreach ($dates as $date) {
                $items->push([
                    'id' => $payment->id,
                    'name' => $payment->name,
                    'amount' => (float) $payment->price,
                    'date' => $date->toDateString(),
                    'frequency' => $payment->frequency,
                    'shopping_type' => $payment->shoppingType?->description,
                    'shop' => $payment->shop?->name,
                    'group' => $payment->shoppingGroup?->name,
                ]);
            }
        }

        return $items->sortBy('date')->values();
    }

    private function upcomingBills(array $groupIds, Carbon $from, int $days): Collection
    {
        $until = $from->copy()->addDays($days);
        $payments = DdPayment::query()
            ->with(['shoppingType', 'shop', 'shoppingGroup'])
            ->whereIn('shopping_group_id', $groupIds)
            ->where('is_active', true)
            ->get();

        $items = collect();

        foreach ($payments as $payment) {
            $dates = $this->scheduleDates(
                Carbon::parse($payment->payment_date),
                $payment->frequency ?: 'monthly',
                $from,
                $until,
                $payment->end_date ? Carbon::parse($payment->end_date) : null
            );

            foreach ($dates as $date) {
                $items->push([
                    'id' => $payment->id,
                    'name' => $payment->name,
                    'amount' => (float) $payment->price,
                    'date' => $date->toDateString(),
                    'frequency' => $payment->frequency,
                    'shopping_type' => $payment->shoppingType?->description,
                    'shop' => $payment->shop?->name,
                    'group' => $payment->shoppingGroup?->name,
                ]);
            }
        }

        return $items->sortBy('date')->values()->take(20);
    }

    private function variableDailyAverage(array $groupIds, Carbon $asOf): float
    {
        $windowStart = $asOf->copy()->subDays(90);
        $total = (float) Receipt::query()
            ->whereIn('shopping_group_id', $groupIds)
            ->whereBetween('date', [$windowStart->toDateString(), $asOf->toDateString()])
            ->whereNull('dd_payment_id')
            ->sum('price');

        return round($total / 90, 4);
    }

    private function categoryBreakdown(array $groupIds, Carbon $monthStart, Carbon $asOf): array
    {
        $spent = Receipt::query()
            ->selectRaw('shopping_type_id, SUM(price) as total_price')
            ->with('shopping_type')
            ->whereIn('shopping_group_id', $groupIds)
            ->whereBetween('date', [$monthStart->toDateString(), $asOf->toDateString()])
            ->groupBy('shopping_type_id')
            ->orderByDesc('total_price')
            ->get();

        $budgets = Budget::query()
            ->with('shoppingType')
            ->whereIn('shopping_group_id', $groupIds)
            ->get()
            ->groupBy('shopping_type_id')
            ->map(fn ($rows) => (float) $rows->sum('amount'));

        return $spent->map(function ($row) use ($budgets) {
            $typeId = $row->shopping_type_id;

            return [
                'shopping_type_id' => $typeId,
                'label' => $row->shopping_type?->description ?? 'Unknown',
                'spent' => (float) $row->total_price,
                'budget' => (float) ($budgets[$typeId] ?? 0),
            ];
        })->values()->all();
    }

    private function monthlyHistory(array $groupIds, Carbon $asOf, int $months): array
    {
        $history = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = $asOf->copy()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $history[] = [
                'label' => $start->format('M Y'),
                'total' => (float) Receipt::query()
                    ->whereIn('shopping_group_id', $groupIds)
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->sum('price'),
            ];
        }

        return $history;
    }

    private function topShops(array $groupIds, Carbon $monthStart, Carbon $asOf): array
    {
        return Receipt::query()
            ->selectRaw('shop_id, SUM(price) as total_price')
            ->with('shop')
            ->whereIn('shopping_group_id', $groupIds)
            ->whereBetween('date', [$monthStart->toDateString(), $asOf->toDateString()])
            ->groupBy('shop_id')
            ->orderByDesc('total_price')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->shop?->name ?? 'Unknown',
                'total' => (float) $row->total_price,
            ])
            ->all();
    }

    private function scheduleDates(Carbon $anchor, string $frequency, Carbon $from, Carbon $until, ?Carbon $endDate): Collection
    {
        $dates = collect();
        $cursor = $anchor->copy()->startOfDay();

        // Walk forward from the stored next/anchor date; also walk backward a few steps
        // so a monthly bill whose next_date is later this month still appears.
        $probe = $cursor->copy();
        for ($i = 0; $i < 36; $i++) {
            if ($endDate && $probe->gt($endDate)) {
                break;
            }
            if ($probe->lt($from)) {
                $probe = $this->addFrequency($probe, $frequency);
                continue;
            }
            if ($probe->gt($until)) {
                break;
            }
            $dates->push($probe->copy());
            $probe = $this->addFrequency($probe, $frequency);
        }

        // Include occurrences before the stored payment_date within the window
        // by stepping backward from the anchor until before $from.
        $back = $this->subFrequency($cursor->copy(), $frequency);
        for ($i = 0; $i < 36; $i++) {
            if ($endDate && $back->gt($endDate)) {
                $back = $this->subFrequency($back, $frequency);
                continue;
            }
            if ($back->lt($from)) {
                break;
            }
            if ($back->lte($until)) {
                $dates->push($back->copy());
            }
            $back = $this->subFrequency($back, $frequency);
        }

        return $dates->unique(fn (Carbon $d) => $d->toDateString())->sortBy(fn (Carbon $d) => $d->timestamp)->values();
    }

    private function occurrencesInRange(Carbon $anchor, string $frequency, Carbon $from, Carbon $until): int
    {
        return $this->scheduleDates($anchor, $frequency, $from, $until, null)->count();
    }

    private function addFrequency(Carbon $date, string $frequency): Carbon
    {
        return match ($frequency) {
            'daily' => $date->copy()->addDay(),
            'weekly' => $date->copy()->addWeek(),
            'fortnightly' => $date->copy()->addWeeks(2),
            'quarterly' => $date->copy()->addMonths(3),
            'half-yearly' => $date->copy()->addMonths(6),
            'yearly' => $date->copy()->addYear(),
            default => $date->copy()->addMonth(),
        };
    }

    private function subFrequency(Carbon $date, string $frequency): Carbon
    {
        return match ($frequency) {
            'daily' => $date->copy()->subDay(),
            'weekly' => $date->copy()->subWeek(),
            'fortnightly' => $date->copy()->subWeeks(2),
            'quarterly' => $date->copy()->subMonths(3),
            'half-yearly' => $date->copy()->subMonths(6),
            'yearly' => $date->copy()->subYear(),
            default => $date->copy()->subMonth(),
        };
    }
}

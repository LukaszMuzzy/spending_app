<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DdPayment;
use App\Models\Receipt;
use Carbon\Carbon;

class CheckDdPayments extends Command
{
    protected $signature = 'check:ddpayments';

    protected $description = 'Check all active DD Payments and insert them into Receipts if due';

    public function handle()
    {
        $today = Carbon::now()->startOfDay();
        $ddPayments = DdPayment::with('shoppingGroup.users')->where('is_active', true)->get();

        foreach ($ddPayments as $ddPayment) {
            if ($ddPayment->end_date && Carbon::parse($ddPayment->end_date)->lt($today)) {
                continue;
            }

            $paymentDate = Carbon::parse($ddPayment->payment_date)->startOfDay();
            if (! $paymentDate->equalTo($today) && ! $this->isDueToday($ddPayment, $today)) {
                continue;
            }

            if ($this->receiptExists($ddPayment, $today)) {
                continue;
            }

            $userId = $ddPayment->shoppingGroup?->users?->first()?->id;
            if (! $userId) {
                $this->warn("Skipping DD payment {$ddPayment->id}: no user on shopping group.");
                continue;
            }

            Receipt::create([
                'note' => $ddPayment->name,
                'date' => $today->toDateString(),
                'price' => $ddPayment->price,
                'shopping_group_id' => $ddPayment->shopping_group_id,
                'shop_id' => $ddPayment->shop_id,
                'payment_method_id' => $ddPayment->payment_method_id,
                'shopping_type_id' => $ddPayment->shopping_type_id,
                'user_id' => $userId,
                'dd_payment_id' => $ddPayment->id,
                'source' => 'manual',
                'needs_review' => false,
            ]);

            $ddPayment->payment_date = $this->nextPaymentDate($paymentDate, $ddPayment->frequency ?: 'monthly');
            $ddPayment->save();
        }

        return Command::SUCCESS;
    }

    private function isDueToday(DdPayment $ddPayment, Carbon $today): bool
    {
        $anchor = Carbon::parse($ddPayment->payment_date)->startOfDay();
        $frequency = $ddPayment->frequency ?: 'monthly';

        if ($anchor->gt($today)) {
            return false;
        }

        return match ($frequency) {
            'daily' => true,
            'weekly' => $anchor->dayOfWeek === $today->dayOfWeek,
            'fortnightly' => $anchor->diffInDays($today) % 14 === 0,
            'monthly' => $anchor->day === min($today->day, $today->daysInMonth) || $anchor->day === $today->day,
            'quarterly' => $anchor->day === $today->day && (($today->month - $anchor->month + 12) % 3 === 0),
            'half-yearly' => $anchor->day === $today->day && (($today->month - $anchor->month + 12) % 6 === 0),
            'yearly' => $anchor->isSameDay($today->copy()->setYear($anchor->year)),
            default => false,
        };
    }

    private function nextPaymentDate(Carbon $from, string $frequency): Carbon
    {
        return match ($frequency) {
            'daily' => $from->copy()->addDay(),
            'weekly' => $from->copy()->addWeek(),
            'fortnightly' => $from->copy()->addWeeks(2),
            'quarterly' => $from->copy()->addMonths(3),
            'half-yearly' => $from->copy()->addMonths(6),
            'yearly' => $from->copy()->addYear(),
            default => $from->copy()->addMonth(),
        };
    }

    private function receiptExists(DdPayment $ddPayment, Carbon $today): bool
    {
        return Receipt::where('dd_payment_id', $ddPayment->id)
            ->whereDate('date', $today->toDateString())
            ->exists();
    }
}

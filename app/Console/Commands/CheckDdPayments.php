<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DdPayment;
use App\Models\Receipt;
use Carbon\Carbon;

class CheckDdPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:ddpayments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all active DD Payments and insert them into Receipts if the payment day is the same as the current day';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now()->startOfDay();
        $ddPayments = DdPayment::where('is_active', true)->get();

        foreach ($ddPayments as $ddPayment) {
            $shouldInsert = false;
            $paymentDate = Carbon::parse($ddPayment->payment_date); // Ensure payment_date is a Carbon instance
            $nextPaymentDate = $paymentDate;

            switch ($ddPayment->frequency) {
                case 'daily':
                    $nextPaymentDate = $today;
                    $shouldInsert = !$this->receiptExists($ddPayment, $today);
                    break;
                case 'weekly':
                    $nextPaymentDate = $paymentDate->copy()->addWeek();
                    $shouldInsert = !$this->receiptExists($ddPayment, $today->copy()->subWeek());
                    break;
                case 'fortnightly':
                    $nextPaymentDate = $paymentDate->copy()->addWeeks(2);
                    $shouldInsert = !$this->receiptExists($ddPayment, $today->copy()->subWeeks(2));
                    break;
                case 'monthly':
                    $nextPaymentDate = $paymentDate->copy()->addMonth();
                    $shouldInsert = !$this->receiptExists($ddPayment, $today->copy()->subMonth());
                    break;
                case 'quarterly':
                    $nextPaymentDate = $paymentDate->copy()->addMonths(3);
                    $shouldInsert = !$this->receiptExists($ddPayment, $today->copy()->subMonths(3));
                    break;
                case 'half-yearly':
                    $nextPaymentDate = $paymentDate->copy()->addMonths(6);
                    $shouldInsert = !$this->receiptExists($ddPayment, $today->copy()->subMonths(6));
                    break;
                case 'yearly':
                    $nextPaymentDate = $paymentDate->copy()->addYear();
                    $shouldInsert = !$this->receiptExists($ddPayment, $today->copy()->subYear());
                    break;
                default:
                    $shouldInsert = false;
                    break;
            }

            if ($shouldInsert) {
                Receipt::create([
                    'note' => $ddPayment->name,
                    'date' => $paymentDate,
                    'price' => $ddPayment->price,
                    'shopping_group_id' => $ddPayment->shopping_group_id,
                    'shop_id' => $ddPayment->shop_id,
                    'payment_method_id' => $ddPayment->payment_method_id,
                    'shopping_type_id' => $ddPayment->shopping_type_id,
                    'user_id' => 1,
                    'dd_payment_id' => $ddPayment->id
                ]);

                // Update the payment_date to the next period
                $ddPayment->payment_date = $nextPaymentDate;
                $ddPayment->save();
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Check if a receipt already exists for the given DdPayment within the specified period.
     *
     * @param DdPayment $ddPayment
     * @param Carbon $periodStart
     * @return bool
     */
    private function receiptExists(DdPayment $ddPayment, Carbon $periodStart): bool
    {
        return Receipt::where('dd_payment_id', $ddPayment->id)
            ->where('date', '>=', $periodStart)
            ->exists();
    }
}
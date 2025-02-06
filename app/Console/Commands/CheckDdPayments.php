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
        $ddPayments = DdPayment::where('is_active', true)
            ->whereDate('payment_date', $today)
            ->get();

        foreach ($ddPayments as $ddPayment) {
            Receipt::create([
                'note' => $ddPayment->name,
                'date' => $ddPayment->payment_date,
                'price' => $ddPayment->price,
                'shopping_group_id' => $ddPayment->shopping_group_id,
                'shop_id' => $ddPayment->shop_id,
                'payment_method_id' => $ddPayment->payment_method_id,
                'shopping_type_id' => $ddPayment->shopping_type_id,
                'user_id' => 1,
                'dd_payment_id' => $ddPayment->id
            ]);
        }

        return Command::SUCCESS;
    }
}
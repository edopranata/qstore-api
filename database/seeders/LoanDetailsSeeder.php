<?php

namespace Database\Seeders;

use App\Http\Controllers\Traits\InvoiceTrait;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Loan;
use Faker\Factory;
use Illuminate\Database\Seeder;

class LoanDetailsSeeder extends Seeder
{
    use InvoiceTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $type = 'LN';
        $faker = Factory::create();
        $loans = Loan::query()->get();
        foreach ($loans as $loan) {
            $trade_date = now()->subDays(rand(10,100));
            $sequence = $this->getLastSequence($trade_date, $type);
            $invoice_number = 'MM'.$type. $trade_date->format('Y') . sprintf('%08d', $sequence);

            $details = $loan->details()->create([
                'trade_date' => $trade_date,
                'opening_balance' => $loan->balance,
                'balance' => $faker->randomElement([-1500000, -1000000, -2000000, -1800000])
            ]);

            $loan->update([
                'balance' => $loan->details()->sum('balance')
            ]);

            $invoice = Invoice::query()
                ->create([
                    'user_id' => 1,
                    'trade_date' => $trade_date,
                    'customer_id' => $loan->customer_id,
                    'customer_type' => get_class(new Customer()),
                    'invoice_number' => $invoice_number,
                    'type' => $type,
                    'sequence' => $sequence,
                ]);

            $invoice->loan()->create([
                'loan_details_id'    => $details->id
            ]);
        }
    }
}

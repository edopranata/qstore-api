<?php

namespace Database\Seeders;

use App\Http\Controllers\Traits\InvoiceTrait;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Loan;
use Faker\Factory;
use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    use InvoiceTrait;
    /**
     * Run the database seeds.
     */
    private int $sequence = 1;
    public function run(): void
    {
        $type = 'LN';

        $faker = Factory::create();
        Customer::query()->inRandomOrder()->take(rand(5, 15))->get()->each(function ($customer) use ($faker, $type){
            $trade_date = now()->subDays(rand(2,15));

            $invoice_number = 'MM'.$type. $trade_date->format('Y') . sprintf('%08d', $this->sequence);

            $balance = $faker->randomElement([30000000, 20000000, 15000000, 50000000, 35000000]);
            $loan = Loan::query()->create([
                'person_id' => $customer->id,
                'person_type' => get_class(new Customer()),
                'balance' => $balance,
            ]);

            $details = $loan->details()->create([
                'trade_date' => $loan->created_at,
                'opening_balance' => 0,
                'balance' => $balance
            ]);

            $invoice = Invoice::query()
                ->create([
                    'user_id' => 1,
                    'trade_date' => $trade_date,
                    'customer_id' => $customer->id,
                    'customer_type' => get_class(new Customer()),
                    'invoice_number' => $invoice_number,
                    'type' => $type,
                    'sequence' => $this->sequence,
                ]);

            $invoice->loan()->create([
                'loan_details_id'    => $details->id
            ]);
            $this->sequence++;
        });



        Driver::query()->inRandomOrder()->take(rand(3, 7))->get()->each(function ($driver) use ($faker, $type){
            $trade_date = now()->subDays(rand(2,15));

            $invoice_number = 'MM'.$type. $trade_date->format('Y') . sprintf('%08d', $this->sequence);

            $balance = $faker->randomElement([30000000, 20000000, 15000000, 50000000, 35000000]);
            $loan = Loan::query()->create([
                'person_id' => $driver->id,
                'person_type' => get_class(new Driver()),
                'balance' => $balance,
            ]);

            $details = $loan->details()->create([
                'trade_date' => $loan->created_at,
                'opening_balance' => 0,
                'balance' => $balance
            ]);

            $invoice = Invoice::query()
                ->create([
                    'user_id' => 1,
                    'trade_date' => $trade_date,
                    'customer_id' => $driver->id,
                    'customer_type' => get_class(new Driver()),
                    'invoice_number' => $invoice_number,
                    'type' => $type,
                    'sequence' => $this->sequence,
                ]);

            $invoice->loan()->create([
                'loan_details_id'    => $details->id
            ]);
            $this->sequence++;
        });
    }
}

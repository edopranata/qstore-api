<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Loan;
use Faker\Factory;
use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();
        $customers = Customer::query()->inRandomOrder()->take(rand(5, 15))->get();

        foreach ($customers as $customer) {
            $balance = $faker->randomElement([30000000, 20000000, 15000000, 50000000, 35000000]);
            $loan = Loan::query()->create([
                'person_id' => $customer->id,
                'person_type' => get_class(new Customer()),
                'balance' => $balance,
            ]);

            $loan->details()->create([
                'trade_date' => $loan->created_at,
                'opening_balance' => 0,
                'balance' => $balance
            ]);
        }

    }
}

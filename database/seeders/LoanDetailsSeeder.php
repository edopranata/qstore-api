<?php

namespace Database\Seeders;

use App\Models\Loan;
use Faker\Factory;
use Illuminate\Database\Seeder;

class LoanDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();
        Loan::query()->get()->each(function (Loan $loan) use ($faker) {
            $loan->details()->create([
                'trade_date' => now(),
                'opening_balance' => $loan->balance,
                'balance' => $faker->randomElement([-1500000, -1000000, -2000000, -1800000])
            ]);

            $loan->update([
                'balance' => $loan->details()->sum('balance')
            ]);
        });
    }
}

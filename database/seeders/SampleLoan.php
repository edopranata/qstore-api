<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SampleLoan extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            LoanSeeder::class,
            LoanDetailsSeeder::class,
        ]);
    }
}

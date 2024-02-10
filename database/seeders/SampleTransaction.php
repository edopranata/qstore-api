<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SampleTransaction extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DeliveryOrderSeeder::class,
            TradingSeeder::class,
            TradingDetailsSeeder::class,
            PlantationSeeder::class,
            PlantationDetailsSeeder::class,
            LoanSeeder::class,
            LoanDetailsSeeder::class,
        ]);
    }
}

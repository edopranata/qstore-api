<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SampleCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            TradingCostSeeder::class,
            CarCostSeeder::class,
            LandCostSeeder::class
        ]);
    }
}

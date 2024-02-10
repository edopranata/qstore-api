<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SampleMaster extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CarsSeeder::class,
            CustomersSeeder::class,
            DriversSeeder::class,
            AreasSeeder::class,
        ]);
    }
}

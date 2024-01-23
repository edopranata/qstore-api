<?php

namespace Database\Seeders;

use App\Models\DeliveryOrder;
use Illuminate\Database\Seeder;

class DeliveryOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeliveryOrder::factory(rand(20, 150))->create();
    }
}

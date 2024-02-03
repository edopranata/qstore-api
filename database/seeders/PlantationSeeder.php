<?php

namespace Database\Seeders;

use App\Models\Plantation;
use Illuminate\Database\Seeder;

class PlantationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $periods = [15, 30, 45, 60, 75, 90, 105, 120];
        foreach ($periods as $period) {
            Plantation::factory()->create([
                'trade_date' => $now->subDays($period)->subHours(rand(1, 12))->subMinutes(rand(1, 59))->subSeconds(rand(1, 59))
            ]);
        }

    }
}

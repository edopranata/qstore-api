<?php

namespace Database\Seeders;

use App\Models\Land;
use App\Models\Plantation;
use App\Models\PlantationDetails;
use Illuminate\Database\Seeder;

class PlantationDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plantation::query()->each(function (Plantation $plantation) {
            $lands = Land::query()->inRandomOrder()->take(rand(2, 4))->get();
            foreach ($lands as $land) {
                PlantationDetails::factory(1)->create([
                    'plantation_id' => $plantation->id,
                    'land_id' => $land->id,
                    'wide' => $land->wide,
                    'trees' => $land->trees
                ]);
            }
            $one = [2020, 2080, 2130, 2180, 2260, 2290, 2310, 2350, 2400];
            $two = [10, 20, 30, 40, 50, 60, 70, 80, 90];
            $price = $one[array_rand($one)] + $two[array_rand($two)];

            $details = $plantation->details();
            $net_weight = rand(10, 20) * $details->sum('trees');

            $plantation->update([
                'net_weight' => $net_weight,
                'net_price' => $price,
                'net_total' => $price * $net_weight,
                'wide_total' => $details->sum('wide'),
                'trees_total' => $details->sum('trees'),
            ]);
            $margin = 40;
            $net_price = $plantation->net_price + $margin;
            $plantation->order()
                ->create([
                    'user_id' => $plantation->user_id,
                    'delivery_date' => $plantation->trade_date,
                    'net_weight' => $plantation->net_weight,
                    'net_price' => $net_price,
                    'margin' => $margin,
                    'gross_total' => $net_price * $plantation->net_weight,
                    'net_total' => $margin * $plantation->net_weight,
                ]);

        });

    }
}

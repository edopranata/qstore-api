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

            $details = $plantation->details();

            $one = [2020, 2080, 2130, 2180, 2260, 2290, 2310, 2350, 2400];
            $two = [10, 20, 30, 40, 50, 60, 70, 80, 90];

            $price = $one[array_rand($one)] + $two[array_rand($two)];
            $trade_cost = 270000;
            $net_weight = rand(10, 20) * $details->sum('trees');

            $driver_fee = 20;
            $car_fee = 80;

            $driver_cost = $driver_fee * $net_weight;
            $car_cost = $car_fee * $net_weight;

            $net_total = $price * $net_weight;

            $gross_total = $trade_cost + $driver_cost + $car_cost;

            $net_income = $net_total - $gross_total;
            $plantation->update([
                'trade_cost' => $trade_cost,
                'net_weight' => $net_weight,
                'net_price' => $price,
                'net_total' => $net_total,
                'wide_total' => $details->sum('wide'),
                'trees_total' => $details->sum('trees'),
                'net_income' => $net_income
            ]);
            $margin = 25;
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

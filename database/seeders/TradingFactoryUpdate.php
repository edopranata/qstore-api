<?php

namespace Database\Seeders;

use App\Models\Trading;
use Illuminate\Database\Seeder;

class TradingFactoryUpdate extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Trading::query()
            ->whereNull('trade_status')
            ->get()->each(function ($trade) {
                $detail = $trade->details()->get();
                $customer_average_price = $detail->count() > 0 ? $detail->avg('price') : 0;
                $customer_total_price = $detail->count() > 0 ? $detail->sum('total') : 0;
                $customer_total_weight = $detail->count() > 0 ? $detail->sum('weight') : 0;

                $net_weight = $customer_total_weight + rand(400, 600);
                $net_price = (int)$customer_average_price + rand(250, 350);
                $driver_cost = $net_weight * $trade->driver_fee;
                $car_cost = $net_weight * $trade->car_fee;
                $loader_cost = $net_weight * $trade->loader_fee;

                $cost_total = $driver_cost + $loader_cost + $car_cost + $trade->trade_cost + $trade->car_transport;

                $gross_total = $net_price * $net_weight;
                $net_income = $gross_total - ($cost_total + $customer_total_price);
                $margin = $net_price > 0 ? $net_price - $customer_average_price : 0;

                $trade->update([
                    'margin' => $margin,
                    'net_weight' => $net_weight,
                    'net_price' => $net_price,
                    'gross_total' => $gross_total,
                    'cost_total' => $cost_total,
                    'net_income' => $net_income,
                    'trade_status' => now(),
                ]);

            });
    }
}

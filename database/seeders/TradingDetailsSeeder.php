<?php

namespace Database\Seeders;

use App\Models\Trading;
use App\Models\TradingDetails;
use Illuminate\Database\Seeder;

class TradingDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Trading::query()->each(function ($trade) {
            TradingDetails::factory(rand(2, 5))->create([
                'trading_id' => $trade->id,
                'trade_date' => $trade->trade_date
            ]);
            $avg_price =  $trade->details()->avg('price');

            $trade->update([
                'margin' => $trade->net_price > 0 ? $trade->net_price  - $avg_price : 0,
                'customer_average_price' => $avg_price,
                'customer_total_price' => $trade->details()->sum('total'),
                'customer_total_weight' => $trade->details()->sum('weight')
            ]);

        });
    }
}

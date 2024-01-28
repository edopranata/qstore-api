<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Trading;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class TradingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $periods = CarbonPeriod::create($now->subDays(rand(20, 100)), now());
        foreach ($periods as $period) {
            $period->subHours(rand(1, 12))->subMinutes(rand(1, 59))->subSeconds(rand(1, 59));
            Trading::factory()->times(rand(2, 5))->create([
                'trade_date' => $period
            ])->each(function ($trade) use ($period) {
                $customers = Customer::query()->where('type', 'farmers')->inRandomOrder()->take(rand(3, 10))->get();
                foreach ($customers as $customer) {
                    $weight = random_int(1000, 3000);
                    $one = [2020, 2080, 2130, 2180, 2260, 2290, 2310, 2350, 2400];
                    $two = [10, 20, 30, 40, 50, 60, 70, 80, 90];
                    $price = $one[array_rand($one)] + $two[array_rand($two)];
                    $trade->details()
                        ->create([
                            'trade_date' => $period,
                            'customer_id' => $customer->id,
                            'weight' => $weight,
                            'price' => $price,
                            'total' => $weight * $price,
                        ]);
                }
                $trade->update(['customer_average_price' => $trade->details()->avg('price')]);
                $trade->update(['customer_total_price' => $trade->details()->sum('total')]);
                $trade->update(['customer_total_weight' => $trade->details()->sum('weight')]);
            });
        }
    }
}

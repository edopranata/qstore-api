<?php

namespace Database\Seeders;

use App\Models\Cost;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Factory;
use Illuminate\Database\Seeder;

class TradingCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $faker = Factory::create();

        $periods = CarbonPeriod::create($now->subDays(rand(10, 20)), now());
        foreach ($periods as $period) {
            $period->subHours(rand(1, 12))->subMinutes(rand(1, 59))->subSeconds(rand(1, 59));
            Cost::query()->create([
                'user_id' => 1,
                'type' => 'trading',
                'trade_date' => Carbon::now()->subDays(rand(5,200)),
                'category' => $faker->randomElement(['muat', 'pembelian', 'lainnya']),
                'description' => $faker->sentence(rand(3,5)),
                'amount' => rand(2,5) * 150000
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

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
            ]);
        }
    }
}

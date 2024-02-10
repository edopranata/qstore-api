<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sets = [
            ['name' => 'car_fee', 'value' => 80],
            ['name' => 'driver_fee', 'value' => 20],
            ['name' => 'trade_cost', 'value' => 220000],
            ['name' => 'do_margin', 'value' => 25],
        ];

        Setting::query()->insert($sets);
    }
}

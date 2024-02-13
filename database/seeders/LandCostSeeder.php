<?php

namespace Database\Seeders;

use App\Models\Land;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;

class LandCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lands = Land::query()->inRandomOrder()->take(5)->get();
        $faker = Factory::create();
        foreach ($lands as $land) {
            $land->costs()->create([
                'user_id' => 1,
                'type' => 'land',
                'trade_date' => Carbon::now()->subDays(rand(5,200)),
                'category' => $faker->randomElement(['pupuk', 'suling', 'lainnya']),
                'description' => $faker->sentence(rand(3,5)),
                'amount' => $land->trees * 20000
            ]);
        }
    }
}

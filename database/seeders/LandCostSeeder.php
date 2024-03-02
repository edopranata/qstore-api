<?php

namespace Database\Seeders;

use App\Models\CostType;
use App\Models\Land;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;

class LandCostSeeder extends Seeder
{
    private string $type = 'plantation';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lands = Land::query()->inRandomOrder()->take(5)->get();
        $ids = CostType::query()->where('type', $this->type)->get()->pluck('id')->toArray();

        $faker = Factory::create();
        foreach ($lands as $land) {
            $land->costs()->create([
                'user_id' => 1,
                'cost_type_id' => $faker->randomElement($ids),
                'trade_date' => Carbon::now()->subDays(rand(5,200)),
                'description' => $faker->sentence(rand(3,5)),
                'amount' => $land->trees * 20000,
                'trees' => $land->trees,
                'wide' => $land->wide,
            ]);
        }
    }
}

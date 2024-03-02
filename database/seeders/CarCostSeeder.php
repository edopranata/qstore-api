<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CostType;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;

class CarCostSeeder extends Seeder
{
    private string $type = 'car';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cars = Car::query()->where('status', 'yes')->inRandomOrder()->take(5)->get();
        $ids = CostType::query()->where('type', $this->type)->get()->pluck('id')->toArray();

        $faker = Factory::create();
        foreach ($cars as $car) {
            $car->costs()->create([
                'user_id' => 1,
                'cost_type_id' => $faker->randomElement($ids),
                'trade_date' => Carbon::now()->subDays(rand(5,200)),
                'description' => $faker->sentence(rand(3,5)),
                'amount' => rand(2,5) * 150000
            ]);
        }
    }
}

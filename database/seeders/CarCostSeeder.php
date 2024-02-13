<?php

namespace Database\Seeders;

use App\Models\Car;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;

class CarCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cars = Car::query()->where('status', 'yes')->inRandomOrder()->take(5)->get();
        $faker = Factory::create();
        foreach ($cars as $car) {
            $car->costs()->create([
                'user_id' => 1,
                'type' => 'car',
                'trade_date' => Carbon::now()->subDays(rand(5,200)),
                'category' => $faker->randomElement(['jasa', 'pembelian', 'lainnya']),
                'description' => $faker->sentence(rand(3,5)),
                'amount' => rand(2,5) * 150000
            ]);
        }
    }
}

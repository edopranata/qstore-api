<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plantation>
 */
class PlantationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $settings = Setting::all();

        $price = collect($settings)->mapWithKeys(function ($item, int $key) {
            return [$item['name'] => $item['value']];
        });
        return [
            'car_id' => Car::query()->inRandomOrder()->first()->id,
            'driver_id' => Driver::query()->inRandomOrder()->first()->id,
            'user_id' => 1,
            'trade_cost' => $price['trade_cost'],
            'car_transport' => $price['car_transport'],
            'car_fee' => $price['car_fee'],
            'loader_fee' => $price['loader_fee'],
            'driver_fee' => $price['driver_fee'],
        ];
    }
}

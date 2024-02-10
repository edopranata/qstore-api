<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Driver;
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
        return [
            'car_id' => Car::query()->inRandomOrder()->first()->id,
            'driver_id' => Driver::query()->inRandomOrder()->first()->id,
            'user_id' => rand(1, 5),
        ];
    }
}

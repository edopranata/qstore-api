<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Land>
 */
class LandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $wide = rand(1, 10);
        return [
            'user_id'   => User::query()->first()->id,
            'name' => $this->faker->unique()->city(),
            'wide'  => $wide,
            'trees' => $wide * 120,
        ];
    }
}

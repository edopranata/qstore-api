<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => $this->faker->unique()->name(),
            'type'      => $this->faker->randomElement(['farmer', 'collector']),
            'phone'     => $this->faker->phoneNumber(),
            'address'   => $this->faker->address(),
            'distance'  => $this->faker->randomNumber(2, true)
        ];
    }
}

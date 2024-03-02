<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TradingDetails>
 */
class TradingDetailsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $weight = random_int(1000, 3000);
        $one = [2020, 2080, 2130, 2180, 2260, 2290, 2310, 2350, 2400];
        $two = [10, 20, 30, 40, 50, 60, 70, 80, 90];
        $price = $one[array_rand($one)] + $two[array_rand($two)];
        return [
            'customer_id' => Customer::query()->where('type', 'farmer')->inRandomOrder()->first()->id,
            'user_id' => 1,
            'weight' => $weight,
            'price' => $price,
            'total' => $weight * $price,
        ];
    }
}

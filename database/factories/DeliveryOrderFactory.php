<?php

namespace Database\Factories;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryOrder>
 */
class DeliveryOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $net_weight = rand(8000, 14000);
        $net_price = rand(21, 25) * 25 * 4;
        $margin = $this->faker->randomElement([40, 45, 35]);
        $gross_total = $net_weight * $net_price;
        $net_total = $net_weight * $margin;
        $customer = new Customer();
        return [
            'customer_id' => Customer::query()->where('type', 'collector')->inRandomOrder()->first()->id,
            'customer_type' => get_class($customer),
            'user_id' => 1,
            'delivery_date' => Carbon::now()->subDays(rand(10, 30)),
            'net_weight' => $net_weight,
            'net_price' => $net_price,
            'margin' => $margin,
            'gross_total' => $gross_total,
            'net_total' => $net_total,
        ];
    }
}

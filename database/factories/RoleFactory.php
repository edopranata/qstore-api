<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Role;

class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Role::class;
    public function definition(): array
    {
        return [
            'name'  => str($this->faker->unique()->userName())->replace('.', '')->headline()
        ];
    }
}

<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


    public function run(): void
    {
        $counts = rand(30, 100);
        $faker = Factory::create();
        for ($n = 0; $n <= $counts; $n++){
            Role::create([
                'name' => str($faker->unique()->userName())->replace('.', ' ')->headline()
            ]);
        }
    }
}

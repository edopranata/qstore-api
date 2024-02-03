<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Land;
use App\Models\User;
use Illuminate\Database\Seeder;

class AreasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Area::factory(rand(3, 5))
            ->create([
                'user_id' => User::query()->first()->id,
            ])->each(function ($area) {
                Land::factory()
                    ->count(rand(1, 3))
                    ->create([
                        'area_id' => $area->id
                    ]);
            });
    }
}

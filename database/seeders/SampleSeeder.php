<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SampleMaster::class,
            SampleTransaction::class,
        ]);
    }
}

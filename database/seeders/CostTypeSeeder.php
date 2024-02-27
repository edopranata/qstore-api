<?php

namespace Database\Seeders;

use App\Models\CostType;
use Illuminate\Database\Seeder;

class CostTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $type = [
            ['type' => 'car', 'name' => 'Biaya Pembelian Sparepart', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'car', 'name' => 'Biaya Jasa Perbaikan', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'plantation', 'name' =>  'Biaya Pemupukan', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'plantation', 'name' =>  'Biaya Racun', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'trading', 'name' =>  'Biaya Operasional', 'created_at' => now(), 'updated_at' => now()],
        ];

        CostType::query()->insert($type);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plantations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Driver::class)->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(\App\Models\Car::class)->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();

            $table->string('name')->default('Plantation');
            $table->dateTime('trade_date');
            $table->double('trade_cost')->default(0)->comment('Uang jalan');
            $table->double('wide_total')->default(0)->comment('Total luas lahan (sum details)');
            $table->double('trees_total')->default(0)->comment('Total pohon (sum details)');
            $table->integer('net_weight')->default(0)->comment('Berat timbangan pabrik (kg)');
            $table->double('net_price')->default(0)->comment('Harga jual ke DO (Rp)');
            $table->double('net_total')->default(0)->comment('Pendapatan kebun');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantations');
    }
};

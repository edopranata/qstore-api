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
        Schema::create('tradings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Driver::class)->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(\App\Models\Car::class)->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();

            $table->dateTime('trade_date');
            $table->double('trade_cost')->default(0); // Uang Jalan

            // Timbangan Kebuh
            $table->double('customer_average_price')->default(0); // Rata-rata Harga beli dari petani
            $table->double('customer_total_price')->default(0); // Total Harga beli dari petani
            $table->double('customer_total_weight')->default(0); // Total Timbangan kebun

            // Delivery Order
            $table->double('margin')->default(0);

            // Timbangan Pabrik
            $table->double('net_weight')->default(0); // Timbangan pabrik
            $table->double('net_total')->default(0); // Pendapatan Bersih

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tradings');
    }
};

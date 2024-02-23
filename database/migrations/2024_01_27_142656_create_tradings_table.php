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

            $table->string('name')->default('Trading');
            $table->dateTime('trade_date');

            // Timbangan Kebuh
            $table->double('customer_average_price')->default(0)->comment('Rata-rata Harga beli dari petani'); // Rata-rata Harga beli dari petani
            $table->double('customer_total_price')->default(0)->comment('Total Harga beli dari petani'); // Total Harga beli dari petani
            $table->double('customer_total_weight')->default(0)->comment('Total Timbangan kebun'); // Total Timbangan kebun

            // Delivery Order
            $table->double('margin')->default(0)->comment('Margin untuk Delivery Order');

            // Timbangan Pabrik
            $table->double('net_weight')->default(0)->comment('Berat timbangan pabrik'); // Timbangan pabrik
            $table->double('net_price')->default(0)->comment('Harga Pabrik'); // Pendapatan Bersih
            $table->double('gross_total')->default(0)->comment('Pendapatan kotor'); // Pendapatan Bersih

            $table->double('trade_cost')->default(0)->comment('Uang jalan');
            $table->double('car_transport')->default(0)->comment('Biaya Minya');
            $table->double('driver_fee')->default(0)->comment('Uang supir Rp / Kg');
            $table->double('loader_fee')->default(0)->comment('Uang muat Rp / Kg');
            $table->double('car_fee')->default(0)->comment('Amprah Mobil Rp/ Kg');
            $table->double('cost_total')->default(0)->comment('Total Biaya Rp');

            $table->double('net_income')->default(0)->comment('Pendapatan bersih setelah di kurangi semua biaya (Amprah, Upah Supir, Uang Jalan)');

            $table->dateTime('driver_status')->nullable();
            $table->dateTime('car_status')->nullable();
            $table->dateTime('income_status')->nullable();
            $table->dateTime('trade_status')->nullable();
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

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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_type')->nullable();
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained()->nullOnDelete()->cascadeOnDelete();

            $table->dateTime('delivery_date');

            $table->double('net_weight')->default(0)->comment('Berat bersih timbangan pabrik (kg)');
            $table->double('net_price')->default(0)->comment('Harga beli pabrik (Rp)');
            $table->double('margin')->default(0)->comment('Margin / pendapatan DO (Rp)');
            $table->double('gross_total')->default(0)->comment('Pendapatan Kotor  (Rp. Berat * Harga Pabrik)');
            $table->double('net_total')->default(0)->comment('Pendapatan Bersih (Rp. Berat * Margin)');
            $table->dateTime('invoice_status')->nullable()->comment('Tanggal invoice');
            $table->dateTime('income_status')->nullable()->comment('Tanggal uang masuk dari pabrik');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};

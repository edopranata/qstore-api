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
        Schema::create('trading_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Trading::class)->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(\App\Models\Customer::class)->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();

            $table->dateTime('trade_date');
            $table->integer('weight')->comment('Timbangan kebun');
            $table->double('price')->comment('harga beli dari petani (Rp)');
            $table->double('total')->comment('Total diterima oleh petani');
            $table->softDeletes();
            $table->dateTime('farmer_status')->nullable()->comment('Tanggal Invoice petani');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trading_details');
    }
};

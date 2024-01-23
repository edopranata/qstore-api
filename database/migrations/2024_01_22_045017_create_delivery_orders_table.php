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
            $table->foreignIdFor(\App\Models\Customer::class)->nullable()->constrained()->nullOnDelete()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained()->nullOnDelete()->cascadeOnDelete();

            $table->dateTime('delivery_date');

            $table->double('net_weight')->default(0);
            $table->double('net_price')->default(0);
            $table->double('margin')->default(0);
            $table->double('gross_total')->default(0);
            $table->double('net_total')->default(0);
            $table->dateTime('invoice_status')->nullable();
            $table->dateTime('income_status')->nullable();
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

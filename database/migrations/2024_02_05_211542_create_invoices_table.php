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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('customer');
            $table->string('to')->nullable();
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('sequence');
            $table->string('invoice_number');
            $table->dateTime('trade_date');
            $table->string('type');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('costs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(\App\Models\CostType::class)->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->dateTime('trade_date');
            $table->nullableMorphs('subject'); // Customer and Land
            $table->string('category')->nullable();
            $table->text('description');
            $table->double('amount');
            $table->double('trees')->default(0);
            $table->double('wide')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costs');
    }
};

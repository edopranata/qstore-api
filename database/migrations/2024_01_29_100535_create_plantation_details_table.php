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
        Schema::create('plantation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Plantation::class)->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(\App\Models\Land::class)->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();

            $table->double('wide')->default(0)->comment('Luas lahan saat transaksi');
            $table->integer('trees')->default(0)->comment('Jumlah pohon saat transaksi');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantation_details');
    }
};

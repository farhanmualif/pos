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
        Schema::create('mitra', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('userId', 50);
            $table->string('namaMitra', 150);
            $table->string('nomorHp', 50);
            $table->string('validasiMitraId', 50)->nullable();
            $table->string('fotoMitra')->nullable();
            $table->enum('statusMitra', ['0', '1'])->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra');
    }
};

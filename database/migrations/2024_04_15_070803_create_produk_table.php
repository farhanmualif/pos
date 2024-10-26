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
        Schema::create('produk', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->enum("kategori", ["Makanan", "Minuman"]);
            $table->string("namaProduk", 100);
            $table->string("slugProduk");
            $table->enum("status", [1, 0])->default(0);
            $table->double("hargaProduk");
            $table->string("fotoProduk")->nullable();
            $table->string("mitraId", 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};

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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("invoiceId", 50);
            $table->string("xenditId", 50)->nullable();
            $table->string("namaUser", 100);
            $table->string("nomorHpAktif", 50)->nullable();
            $table->double("totalHarga");
            $table->string("usernameKasir", 50);
            $table->string("mitraId", 50);
            $table->string("namaMitra", 150);
            $table->enum("tipeTransaksi", ["CASH", "TRANSFER"])->nullable();
            $table->string("statusOrder", 100)->nullable();
            $table->datetime("tanggalOrder");
            $table->datetime("tanggalBayar")->nullable();
            $table->string("paymentChannel", 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};

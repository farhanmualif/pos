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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("nama", 150);
            $table->string("username", 100);
            $table->string('email', 255)->unique();
            $table->string('password');
            $table->string("foto")->nullable();
            $table->enum("akses", ['1', '2', '3'])->default('1')->comment("Tipe akses pengguna: 1 = Super Admin, 2 = Admin, 3 = Karyawan");
            $table->enum("active", [1, 0])->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

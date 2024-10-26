<?php

namespace Database\Seeders;

use App\Models\Mitra;
use App\Models\Produk;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mitra = Mitra::first();
        Produk::create([
            "kategori" => "Makanan",
            "namaProduk" => "Mie ayam",
            "slugProduk" => "mie-ayam",
            "status" => '1',
            "hargaProduk" => 10000,
            "mitraId" => $mitra['id']
        ]);

        Produk::create([
            "kategori" => "Minuman",
            "namaProduk" => "Good Day Freeze",
            "slugProduk" => "good-day-freeze",
            "status" => '1',
            "hargaProduk" => 30000,
            "mitraId" => $mitra['id']
        ]);
    }
}

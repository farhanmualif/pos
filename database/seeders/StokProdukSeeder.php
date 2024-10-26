<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\StokProduk;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StokProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('akses', 2)->first();
        $produk = Produk::orderBy('namaProduk', 'ASC')->take(2)->get();
        foreach ($produk as $produkItem) {
            StokProduk::create([
                'userId' => $user['id'],
                'produkId' => $produkItem->id,
                'tanggalTransaksi' => '2024-08-01',
                'qty' => 10,
            ]);
        }
    }
}

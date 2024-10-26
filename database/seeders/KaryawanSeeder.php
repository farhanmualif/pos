<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('akses', '3')->first();
        $mitra = Mitra::orderBy('namaMitra', 'ASC')->first();
        Karyawan::create([
            'userId' => $user['id'],
            'mitraId' => $mitra['id'],
            'nomorHpAktif' => '081234567890',
            'alamat' => 'Jl. Jalan No. 1',
        ]);
    }
}

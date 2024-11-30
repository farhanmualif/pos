<?php

namespace Database\Seeders;

use App\Models\Mitra;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MitraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('akses', '2')->first();
        Mitra::create([
            'userId' => $user['id'],
            'namaMitra' => 'Warung Makmur',
            'nomorHp' => '081234567890',
            'validasiMitraId' => '1234567890',
            'statusMitra' => '1',
            'fotoMitra' => 'null',
        ]);
    }
}

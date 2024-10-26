<?php

namespace Database\Seeders;

use App\Models\User;
use  Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'nama' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'username' => 'superadmin',
            'password' => bcrypt('superadmin123'),
            'akses' => 1,
            'active' => 1,
        ]);

        User::create([
            'nama' => 'Mitra Makmur',
            'email' => 'makmur@gmail.com',
            'username' => 'makmur',
            'password' => bcrypt('makmur123'),
            'akses' => 2,
            'active' => 1,
        ]);
        User::create([
            'nama' => 'karyawan makmur',
            'email' => 'karyawan.makmur@gmail.com',
            'username' => 'karyaawan.makmur',
            'password' => bcrypt('karyawan.makmur123'),
            'akses' => 3,
            'active' => 1,
        ]);
    }
}

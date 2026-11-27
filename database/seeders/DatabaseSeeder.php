<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@simata.test'],
            [
                'name' => 'Admin SIMATA',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@simata.test'],
            [
                'name' => 'Pengunjung Wisata',
                'password' => Hash::make('password123'),
                'role' => 'pengunjung',
            ]
        );

        // Data contoh studi kasus Pantai Papuma (destinasi, kuota harian, pesanan)
        $this->call(PapumaSeeder::class);
    }
}
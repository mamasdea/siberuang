<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah sudah ada admin, jika belum maka buat
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // Cek berdasarkan email agar tidak duplikat
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('12345678'), // Ganti dengan password aman
                'role' => 'admin',
            ]
        );
    }
}

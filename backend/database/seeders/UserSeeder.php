<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // <-- Import model User
use Illuminate\Support\Facades\Hash; // <-- Import Hash untuk password

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data user yang ada sebelumnya (opsional, agar seeder bisa dijalankan ulang tanpa duplikasi)
        // User::truncate(); // Hati-hati, ini akan menghapus semua user

        // Data user contoh
        $users = [
            [
                'name' => 'Admin Akuntansiku',
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => 'admin1234', // Password akan di-hash
                'role' => 'admin',
                'status' => 'aktif',
                'email_verified_at' => now(), // Opsional: set email sebagai terverifikasi
            ],
            [
                'name' => 'Direktur Utama',
                'username' => 'direktur',
                'email' => 'direktur@gmail.com',
                'password' => 'direktur1234',
                'role' => 'direktur',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'User Biasa',
                'username' => 'userbiasa',
                'email' => 'user@gmail.com',
                'password' => 'user1234',
                'role' => 'user',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'User Nonaktif',
                'username' => 'usernonaktif',
                'email' => 'nonaktif@gmail.com',
                'password' => 'user1234',
                'role' => 'user',
                'status' => 'nonaktif',
                'email_verified_at' => now(),
            ],
            // Tambahkan data user lain sesuai kebutuhan
        ];

        // Loop melalui data dan buat user
        foreach ($users as $userData) {
            User::create([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']), // Penting: Hash password
                'role' => $userData['role'],
                'status' => $userData['status'],
                'email_verified_at' => $userData['email_verified_at'] ?? null, // Set jika ada
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Anda juga bisa menggunakan User Factory jika ingin data yang lebih dinamis dan banyak
        // Contoh: User::factory()->count(10)->create();
        // Pastikan UserFactory Anda sudah dikonfigurasi dengan benar.
    }
}

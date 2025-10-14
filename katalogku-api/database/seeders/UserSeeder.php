<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test user
        User::create([
            'name' => 'Test User',
            'whatsapp' => '081234567890',
            'password' => Hash::make('password'),
            'verified_at' => now(),
        ]);

        // Create admin user
        User::create([
            'name' => 'Admin',
            'whatsapp' => '081111111111',
            'password' => Hash::make('admin123'),
            'verified_at' => now(),
        ]);
    }
}

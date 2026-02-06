<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if super admin already exists
        $superAdmin = User::where('email', 'super@maboutique.com')->first();

        if (!$superAdmin) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'super@maboutique.com',
                'password' => 'Skypper19@', // Will be hashed by mutator or we should hash it if no mutator
                'role' => 'admin', // Ensure 'admin' is in the Enum
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);
        }
    }
}

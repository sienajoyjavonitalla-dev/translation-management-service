<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Local/dev convenience user for testing auth.
        // Override via env if desired.
        $email = env('SEED_ADMIN_EMAIL', 'admin@example.com');
        $password = env('SEED_ADMIN_PASSWORD', 'password');

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
            ]
        );
    }
}


<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's default admin account.
     */
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@fastagreements.ai'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => Admin::ROLE_SUPER_ADMIN,
                'status' => true,
            ]
        );
    }
}

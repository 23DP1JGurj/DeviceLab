<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@devicelab.local'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('Admin123!'),
                'role' => User::ROLE_ADMIN,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'staff@devicelab.local'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('Staff123!'),
                'role' => User::ROLE_STAFF,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'client@devicelab.local'],
            [
                'name' => 'Client User',
                'password' => Hash::make('Client123!'),
                'role' => User::ROLE_CLIENT,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}

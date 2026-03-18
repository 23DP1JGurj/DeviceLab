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
                'phone' => '+37120000001',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'staff@devicelab.local'],
            [
                'name' => 'Staff User',
                'phone' => '+37120000002',
                'password' => Hash::make('password'),
                'role' => User::ROLE_STAFF,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'client@devicelab.local'],
            [
                'name' => 'Client User',
                'phone' => '+37120000003',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CLIENT,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}

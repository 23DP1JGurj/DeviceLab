<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $staffMembers = [
            ['Andris Kalniņš', 'andris@devicelab.local', '+37120110001', 'phone specialist', 1],
            ['Māris Ozols', 'maris@devicelab.local', '+37120110002', 'laptop specialist', 2],
            ['Kristaps Bērziņš', 'kristaps@devicelab.local', '+37120110003', 'diagnostics', 1],
            ['Daniels Liepa', 'daniels@devicelab.local', '+37120110004', 'motherboard repair', 2],
            ['Rihards Jansons', 'rihards@devicelab.local', '+37120110005', 'data recovery', 1],
            ['Edgars Siliņš', 'edgars@devicelab.local', '+37120110006', 'general technician', 2],
            ['Artūrs Vītols', 'arturs@devicelab.local', '+37120110007', 'mobile repair', 1],
            ['Renārs Pūce', 'renars@devicelab.local', '+37120110008', 'senior technician', 2],
        ];

        DB::table('users')->updateOrInsert(
            ['email' => 'staff@devicelab.local'],
            [
                'name' => 'Staff User',
                'phone' => '+37120000002',
                'password' => Hash::make('password'),
                'role' => User::ROLE_STAFF,
                'specialization' => 'general technician',
                'branch_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        foreach ($staffMembers as [$name, $email, $phone, $specialization, $branchId]) {
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => $phone,
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_STAFF,
                    'specialization' => $specialization,
                    'branch_id' => $branchId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}

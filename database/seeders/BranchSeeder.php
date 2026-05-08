<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('branches')->upsert([
            [
                'id' => 1,
                'name' => 'DeviceLab Centrs',
                'address' => 'Brīvības iela 45, Rīga',
                'phone' => '+371 2000 1234',
                'email' => 'centrs@devicelab.local',
                'working_hours' => 'P-Pk 09:00-18:00',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'DeviceLab Purvciems',
                'address' => 'Dzelzavas iela 72, Rīga',
                'phone' => '+371 2000 5678',
                'email' => 'purvciems@devicelab.local',
                'working_hours' => 'P-S 10:00-19:00',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'DeviceLab Imanta',
                'address' => 'Kurzemes prospekts 88, Rīga',
                'phone' => '+371 2000 2345',
                'email' => 'imanta@devicelab.local',
                'working_hours' => 'P-Pk 09:00-18:00',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'DeviceLab Teika',
                'address' => 'Brīvības gatve 214, Rīga',
                'phone' => '+371 2000 3456',
                'email' => 'teika@devicelab.local',
                'working_hours' => 'P-Pk 09:00-18:00',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'DeviceLab Pārdaugava',
                'address' => 'Kalnciema iela 40, Rīga',
                'phone' => '+371 2000 4567',
                'email' => 'pardaugava@devicelab.local',
                'working_hours' => 'P-Pk 09:00-18:00',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id'], [
            'name',
            'address',
            'phone',
            'email',
            'working_hours',
            'is_active',
            'updated_at',
        ]);
    }
}

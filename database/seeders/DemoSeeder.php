<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $clientId = DB::table('users')
            ->where('email', 'client@devicelab.local')
            ->value('id');

        DB::table('branches')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'DeviceLab Riga Center',
                'address' => 'Riga, Centrs',
                'phone' => '+371 00000000',
                'email' => 'riga@devicelab.local',
                'working_hours' => 'Mo-Fr 9:00-18:00',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'DeviceLab Riga Imanta',
                'address' => 'Riga, Imanta',
                'phone' => '+371 11111111',
                'email' => 'imanta@devicelab.local',
                'working_hours' => 'Mo-Sa 10:00-19:00',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('services')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'Diagnostika',
                'description' => 'Ierices diagnostika',
                'base_price' => 15.00,
                'estimated_minutes' => 30,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Ekrana maina',
                'description' => 'Ekrana nomaina (darbs)',
                'base_price' => 45.00,
                'estimated_minutes' => 90,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('parts')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'iPhone ekrans',
                'sku' => 'IP-SCREEN-001',
                'description' => 'Rezerves ekrans',
                'unit_price' => 80.00,
                'stock_qty' => 5,
                'min_stock_qty' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('devices')->insertOrIgnore([
            [
                'id' => 1,
                'user_id' => $clientId,
                'type' => 'phone',
                'brand' => 'Apple',
                'model' => 'iPhone 12',
                'serial_number' => 'SN-DEMO-001',
                'warranty_until' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => $clientId,
                'type' => 'phone',
                'brand' => 'Samsung',
                'model' => 'Galaxy S21',
                'serial_number' => 'SN-DEMO-002',
                'warranty_until' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

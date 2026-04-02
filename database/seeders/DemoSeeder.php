<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $adminId = DB::table('users')->where('email', 'admin@devicelab.local')->value('id');
        $staffId = DB::table('users')->where('email', 'staff@devicelab.local')->value('id');
        $clientId = DB::table('users')->where('email', 'client@devicelab.local')->value('id');

        DB::table('branches')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'DeviceLab Riga Center',
                'address' => 'Riga, Centrs',
                'phone' => '+371 00000000',
                'email' => 'riga@devicelab.local',
                'working_hours' => 'Mo-Fr 9:00-18:00',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'DeviceLab Riga Imanta',
                'address' => 'Riga, Imanta',
                'phone' => '+371 11111111',
                'email' => 'imanta@devicelab.local',
                'working_hours' => 'Mo-Sa 10:00-19:00',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('services')->upsert([
            ['id' => 1, 'name' => 'Diagnostika', 'description' => 'Ierīces pamatdiagnostika un defekta noteikšana', 'base_price' => 15.00, 'estimated_minutes' => 30, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Ātrā diagnostika', 'description' => 'Ātra sākotnējā pārbaude un servisa rekomendācija', 'base_price' => 10.00, 'estimated_minutes' => 20, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Ekrāna maiņa', 'description' => 'Ekrāna nomaiņas darbs bez detaļas cenas', 'base_price' => 45.00, 'estimated_minutes' => 90, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Akumulatora maiņa', 'description' => 'Akumulatora nomaiņas darbs ar pārbaudi', 'base_price' => 35.00, 'estimated_minutes' => 60, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Uzlādes ligzdas maiņa', 'description' => 'Uzlādes porta pārbaude un nomaiņas darbs', 'base_price' => 40.00, 'estimated_minutes' => 75, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'Mitruma tīrīšana', 'description' => 'Ierīces tīrīšana pēc mitruma bojājuma', 'base_price' => 30.00, 'estimated_minutes' => 90, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'name' => 'Datu atjaunošana', 'description' => 'Datu atjaunošanas sākotnējā pārbaude', 'base_price' => 50.00, 'estimated_minutes' => 120, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'name' => 'Portatīvā datora apkope', 'description' => 'Portatīvā datora tīrīšana un tehniskā apkope', 'base_price' => 45.00, 'estimated_minutes' => 90, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'name' => 'Termopastas maiņa', 'description' => 'Termopastas nomaiņa un dzesēšanas pārbaude', 'base_price' => 25.00, 'estimated_minutes' => 45, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'name' => 'Programmatūras diagnostika', 'description' => 'Operētājsistēmas un programmatūras problēmu pārbaude', 'base_price' => 20.00, 'estimated_minutes' => 45, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ], ['id'], ['name', 'description', 'base_price', 'estimated_minutes', 'is_active', 'updated_at']);

        DB::table('parts')->upsert([
            [
                'id' => 1,
                'name' => 'iPhone ekrāns',
                'sku' => 'IP-SCREEN-001',
                'description' => 'Rezerves ekrāns',
                'unit_price' => 80.00,
                'stock_qty' => 5,
                'min_stock_qty' => 1,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Samsung akumulators',
                'sku' => 'SMS-BAT-001',
                'description' => 'Rezerves akumulators Samsung tālrunim',
                'unit_price' => 29.00,
                'stock_qty' => 7,
                'min_stock_qty' => 1,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'USB-C ligzda',
                'sku' => 'USB-C-PORT-001',
                'description' => 'Rezerves USB-C uzlādes ligzda',
                'unit_price' => 14.50,
                'stock_qty' => 12,
                'min_stock_qty' => 2,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id'], ['name', 'sku', 'description', 'unit_price', 'stock_qty', 'min_stock_qty', 'is_active', 'updated_at']);

        DB::table('devices')->updateOrInsert(
            ['serial_number' => 'SN-ADMIN-001'],
            [
                'user_id' => $adminId,
                'type' => 'laptop',
                'brand' => 'Lenovo',
                'model' => 'ThinkPad X1',
                'warranty_until' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('devices')->updateOrInsert(
            ['serial_number' => 'SN-STAFF-001'],
            [
                'user_id' => $staffId,
                'type' => 'tablet',
                'brand' => 'Apple',
                'model' => 'iPad Air',
                'warranty_until' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('devices')->updateOrInsert(
            ['serial_number' => 'SN-CLIENT-001'],
            [
                'user_id' => $clientId,
                'type' => 'phone',
                'brand' => 'Apple',
                'model' => 'iPhone 12',
                'warranty_until' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('devices')->updateOrInsert(
            ['serial_number' => 'SN-CLIENT-002'],
            [
                'user_id' => $clientId,
                'type' => 'phone',
                'brand' => 'Samsung',
                'model' => 'Galaxy S21',
                'warranty_until' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}

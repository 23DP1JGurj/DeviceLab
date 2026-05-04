<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AuthDemoSeeder::class,
            DemoSeeder::class,
            StaffDemoSeeder::class,
            PhoneModelSeeder::class,
            LaptopModelSeeder::class,
            TabletModelSeeder::class,
            DeviceModelSuggestionSeeder::class,
        ]);
    }
}

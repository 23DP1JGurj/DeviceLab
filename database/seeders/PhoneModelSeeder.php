<?php

namespace Database\Seeders;

use App\Models\PhoneModel;
use Illuminate\Database\Seeder;

class PhoneModelSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/phone_models.csv');

        if (! is_readable($path)) {
            return;
        }

        $handle = fopen($path, 'r');

        if (! $handle) {
            return;
        }

        $now = now();
        $rows = [];

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            $brand = trim((string) ($data[0] ?? ''));
            $model = trim((string) ($data[1] ?? ''));

            if ($brand === '' || $model === '') {
                continue;
            }

            $rows[] = [
                'brand' => $brand,
                'model' => $model,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= 500) {
                PhoneModel::upsert($rows, ['brand', 'model'], ['updated_at']);
                $rows = [];
            }
        }

        fclose($handle);

        if ($rows !== []) {
            PhoneModel::upsert($rows, ['brand', 'model'], ['updated_at']);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\TabletModel;
use Illuminate\Database\Seeder;

class TabletModelSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/tablets_only.csv');

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
                TabletModel::upsert($rows, ['brand', 'model'], ['updated_at']);
                $rows = [];
            }
        }

        fclose($handle);

        if ($rows !== []) {
            TabletModel::upsert($rows, ['brand', 'model'], ['updated_at']);
        }
    }
}

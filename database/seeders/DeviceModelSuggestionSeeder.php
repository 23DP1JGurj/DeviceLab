<?php

namespace Database\Seeders;

use App\Models\DeviceModelSuggestion;
use Illuminate\Database\Seeder;

class DeviceModelSuggestionSeeder extends Seeder
{
    public function run(): void
    {
        $this->importCsv('tablet', 'tablets_only.csv');
        $this->importCsv('laptop', 'laptops_only.csv');
    }

    private function importCsv(string $deviceType, string $fileName): void
    {
        $path = database_path("seeders/data/{$fileName}");

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
                'device_type' => $deviceType,
                'brand' => $brand,
                'model' => $model,
                'popularity' => 1,
                'source' => $fileName,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= 500) {
                DeviceModelSuggestion::upsert(
                    $rows,
                    ['device_type', 'brand', 'model'],
                    ['popularity', 'source', 'updated_at']
                );
                $rows = [];
            }
        }

        fclose($handle);

        if ($rows !== []) {
            DeviceModelSuggestion::upsert(
                $rows,
                ['device_type', 'brand', 'model'],
                ['popularity', 'source', 'updated_at']
            );
        }
    }
}

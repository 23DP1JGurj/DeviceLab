<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class PresentationDemoSeeder extends Seeder
{
    private const PASSWORD = '12345678';

    private int $deviceCount = 0;
    private int $reviewCount = 0;
    private int $paymentCount = 0;

    public function run(): void
    {
        Mail::fake();

        $this->cleanupDemoData();

        $branches = $this->ensureBranches();
        $services = $this->ensureServices();
        $parts = $this->ensureParts();
        $users = $this->createUsers($branches);
        $devices = $this->createDevices($users['clients']);
        $orders = $this->createOrders($users, $devices, $branches, $services, $parts);

        $this->createReviews($orders);
        $this->createNotifications($users, $orders);
        $this->createContactMessages();

        $this->command?->info('Presentation demo data created.');
        $this->command?->line('');
        $this->command?->line('Demo accounts:');
        $this->command?->line('Client: a230245jg@rvt.lv / 12345678');
        $this->command?->line('Staff: staff@gmail.com / 12345678');
        $this->command?->line('Admin: admin@gmail.com / 12345678');
        $this->command?->line('');
        $this->command?->line('Created:');
        $this->command?->line('- 20 clients');
        $this->command?->line('- 5 staff');
        $this->command?->line('- 1 admin');
        $this->command?->line('- 30 orders');
        $this->command?->line("- {$this->deviceCount} devices");
        $this->command?->line("- {$this->reviewCount} reviews");
        $this->command?->line("- {$this->paymentCount} payments");
        $this->command?->line('');
        $this->command?->line('Emails were disabled during seeding.');
    }

    private function cleanupDemoData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ([
            'order_attachments',
            'user_notifications',
            'contact_messages',
            'reviews',
            'payments',
            'order_status_histories',
            'order_items',
            'orders',
            'devices',
            'users',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function ensureBranches(): array
    {
        $now = now();
        $branches = [
            ['name' => 'DeviceLab Centrs', 'address' => 'Brīvības iela 45, Rīga', 'phone' => '+371 2000 1234', 'email' => 'centrs@devicelab.local', 'working_hours' => 'P-Pk 09:00-18:00'],
            ['name' => 'DeviceLab Purvciems', 'address' => 'Dzelzavas iela 72, Rīga', 'phone' => '+371 2000 5678', 'email' => 'purvciems@devicelab.local', 'working_hours' => 'P-S 10:00-19:00'],
            ['name' => 'DeviceLab Imanta', 'address' => 'Kurzemes prospekts 88, Rīga', 'phone' => '+371 2000 2345', 'email' => 'imanta@devicelab.local', 'working_hours' => 'P-Pk 09:00-18:00'],
            ['name' => 'DeviceLab Teika', 'address' => 'Brīvības gatve 214, Rīga', 'phone' => '+371 2000 3456', 'email' => 'teika@devicelab.local', 'working_hours' => 'P-Pk 09:00-18:00'],
            ['name' => 'DeviceLab Āgenskalns', 'address' => 'Kalnciema iela 40, Rīga', 'phone' => '+371 2000 4567', 'email' => 'agenskalns@devicelab.local', 'working_hours' => 'P-Pk 09:00-18:00'],
        ];

        foreach ($branches as $branch) {
            DB::table('branches')->updateOrInsert(
                ['name' => $branch['name']],
                [...$branch, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        return DB::table('branches')
            ->where('is_active', 1)
            ->orderBy('id')
            ->limit(5)
            ->pluck('id')
            ->all();
    }

    private function ensureServices(): array
    {
        $now = now();
        $services = [
            ['name' => 'Diagnostika', 'description' => 'Ierīces pamatdiagnostika un defekta noteikšana.', 'base_price' => 15.00, 'estimated_minutes' => 30],
            ['name' => 'Ātrā diagnostika', 'description' => 'Ātra sākotnējā pārbaude un servisa rekomendācija.', 'base_price' => 20.00, 'estimated_minutes' => 25],
            ['name' => 'Ekrāna maiņa', 'description' => 'Ekrāna nomaiņas darbs bez detaļas cenas.', 'base_price' => 45.00, 'estimated_minutes' => 90],
            ['name' => 'Akumulatora maiņa', 'description' => 'Akumulatora nomaiņas darbs ar pārbaudi.', 'base_price' => 35.00, 'estimated_minutes' => 60],
            ['name' => 'Programmatūras diagnostika', 'description' => 'Operētājsistēmas un programmatūras problēmu pārbaude.', 'base_price' => 25.00, 'estimated_minutes' => 45],
            ['name' => 'Datu atjaunošana', 'description' => 'Datu atjaunošanas sākotnējā pārbaude un darbs.', 'base_price' => 60.00, 'estimated_minutes' => 120],
            ['name' => 'Ligzdas maiņa', 'description' => 'Uzlādes/barošanas porta pārbaude un maiņa.', 'base_price' => 40.00, 'estimated_minutes' => 75],
            ['name' => 'Profilaktiskā apkope', 'description' => 'Tīrīšana, profilakse un tehniskā apkope.', 'base_price' => 35.00, 'estimated_minutes' => 60],
            ['name' => 'Mitruma tīrīšana', 'description' => 'Ierīces tīrīšana pēc mitruma bojājuma.', 'base_price' => 50.00, 'estimated_minutes' => 90],
            ['name' => 'Portatīvā datora apkope', 'description' => 'Portatīvā datora tīrīšana un tehniskā apkope.', 'base_price' => 45.00, 'estimated_minutes' => 90],
        ];

        foreach ($services as $service) {
            DB::table('services')->updateOrInsert(
                ['name' => $service['name']],
                [...$service, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        return DB::table('services')->pluck('id', 'name')->all();
    }

    private function ensureParts(): array
    {
        $now = now();
        $parts = [
            ['name' => 'iPhone ekrāna modulis', 'sku' => 'DEMO-IP-SCREEN', 'description' => 'Rezerves ekrāna modulis iPhone ierīcēm.', 'unit_price' => 89.00, 'stock_qty' => 12, 'min_stock_qty' => 2],
            ['name' => 'Samsung akumulators', 'sku' => 'DEMO-SMS-BAT', 'description' => 'Samsung tālruņu akumulatora modulis.', 'unit_price' => 39.00, 'stock_qty' => 15, 'min_stock_qty' => 2],
            ['name' => 'USB-C ligzda', 'sku' => 'DEMO-USBC-PORT', 'description' => 'USB-C uzlādes ligzda.', 'unit_price' => 18.00, 'stock_qty' => 18, 'min_stock_qty' => 3],
            ['name' => 'Laptop ventilators', 'sku' => 'DEMO-LAP-FAN', 'description' => 'Portatīvā datora dzesēšanas ventilators.', 'unit_price' => 35.00, 'stock_qty' => 8, 'min_stock_qty' => 1],
            ['name' => 'SSD 512GB', 'sku' => 'DEMO-SSD-512', 'description' => '512GB SSD disks.', 'unit_price' => 55.00, 'stock_qty' => 10, 'min_stock_qty' => 2],
            ['name' => 'Planšetes ekrāna modulis', 'sku' => 'DEMO-TAB-SCREEN', 'description' => 'Planšetes ekrāna rezerves modulis.', 'unit_price' => 75.00, 'stock_qty' => 7, 'min_stock_qty' => 1],
            ['name' => 'Termopasta un tīrīšanas komplekts', 'sku' => 'DEMO-THERMAL', 'description' => 'Termopasta un tīrīšanas materiāli.', 'unit_price' => 12.00, 'stock_qty' => 20, 'min_stock_qty' => 4],
            ['name' => 'Klaviatūras modulis', 'sku' => 'DEMO-KBD-MOD', 'description' => 'Portatīvā datora klaviatūras modulis.', 'unit_price' => 45.00, 'stock_qty' => 6, 'min_stock_qty' => 1],
        ];

        foreach ($parts as $part) {
            DB::table('parts')->updateOrInsert(
                ['sku' => $part['sku']],
                [...$part, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        return DB::table('parts')->pluck('id', 'name')->all();
    }

    private function createUsers(array $branchIds): array
    {
        $adminId = $this->insertUser([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'phone' => '+371 20000003',
            'role' => 'admin',
        ]);

        $staffData = [
            ['name' => 'Artūrs Kalniņš', 'email' => 'staff@gmail.com', 'phone' => '+371 20000002', 'specialization' => 'Telefoni un planšetes'],
            ['name' => 'Marta Ozola', 'email' => 'staff2@gmail.com', 'phone' => '+371 20000004', 'specialization' => 'Portatīvie datori'],
            ['name' => 'Deniss Petrovs', 'email' => 'staff3@gmail.com', 'phone' => '+371 20000005', 'specialization' => 'Diagnostika'],
            ['name' => 'Alise Jansone', 'email' => 'staff4@gmail.com', 'phone' => '+371 20000006', 'specialization' => 'Datu atjaunošana'],
            ['name' => 'Roberts Liepa', 'email' => 'staff5@gmail.com', 'phone' => '+371 20000007', 'specialization' => 'Barošanas un ligzdu remonts'],
        ];

        $staff = [];
        foreach ($staffData as $index => $staffMember) {
            $staff[] = $this->insertUser([
                ...$staffMember,
                'role' => 'staff',
                'branch_id' => $branchIds[$index % count($branchIds)],
            ]);
        }

        $clientData = [
            ['name' => 'Jegors Gurjevs', 'email' => 'a230245jg@rvt.lv', 'phone' => '+371 20000001'],
            ['name' => 'Anna Bērziņa', 'email' => 'client1@devicelab.test', 'phone' => '+371 20100001'],
            ['name' => 'Maks Krūmiņš', 'email' => 'client2@devicelab.test', 'phone' => '+371 20100002'],
            ['name' => 'Laura Ozola', 'email' => 'client3@devicelab.test', 'phone' => '+371 20100003'],
            ['name' => 'Rihards Liepiņš', 'email' => 'client4@devicelab.test', 'phone' => '+371 20100004'],
            ['name' => 'Viktorija Petrova', 'email' => 'client5@devicelab.test', 'phone' => '+371 20100005'],
            ['name' => 'Nikita Sokolovs', 'email' => 'client6@devicelab.test', 'phone' => '+371 20100006'],
            ['name' => 'Deniss Ivanovs', 'email' => 'client7@devicelab.test', 'phone' => '+371 20100007'],
            ['name' => 'Marta Jansone', 'email' => 'client8@devicelab.test', 'phone' => '+371 20100008'],
            ['name' => 'Roberts Kalniņš', 'email' => 'client9@devicelab.test', 'phone' => '+371 20100009'],
            ['name' => 'Alise Liepa', 'email' => 'client10@devicelab.test', 'phone' => '+371 20100010'],
            ['name' => 'Kristaps Ozols', 'email' => 'client11@devicelab.test', 'phone' => '+371 20100011'],
            ['name' => 'Marija Siliņa', 'email' => 'client12@devicelab.test', 'phone' => '+371 20100012'],
            ['name' => 'Dmitrijs Pavlovs', 'email' => 'client13@devicelab.test', 'phone' => '+371 20100013'],
            ['name' => 'Elīna Vītola', 'email' => 'client14@devicelab.test', 'phone' => '+371 20100014'],
            ['name' => 'Oskars Briedis', 'email' => 'client15@devicelab.test', 'phone' => '+371 20100015'],
            ['name' => 'Darja Petrova', 'email' => 'client16@devicelab.test', 'phone' => '+371 20100016'],
            ['name' => 'Daniels Jansons', 'email' => 'client17@devicelab.test', 'phone' => '+371 20100017'],
            ['name' => 'Sofija Krūmiņa', 'email' => 'client18@devicelab.test', 'phone' => '+371 20100018'],
            ['name' => 'Andrejs Sokolovs', 'email' => 'client19@devicelab.test', 'phone' => '+371 20100019'],
        ];

        $clients = [];
        foreach ($clientData as $client) {
            $clients[] = $this->insertUser([...$client, 'role' => 'client']);
        }

        return [
            'admin' => $adminId,
            'staff' => $staff,
            'clients' => $clients,
            'presentationClient' => $clients[0],
            'presentationStaff' => $staff[0],
        ];
    }

    private function insertUser(array $data): int
    {
        $now = now();
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make(self::PASSWORD),
            'role' => $data['role'],
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('users', 'is_blocked')) {
            $payload['is_blocked'] = false;
        }

        if (Schema::hasColumn('users', 'specialization')) {
            $payload['specialization'] = $data['specialization'] ?? null;
        }

        if (Schema::hasColumn('users', 'branch_id')) {
            $payload['branch_id'] = $data['branch_id'] ?? null;
        }

        return (int) DB::table('users')->insertGetId($payload);
    }

    private function createDevices(array $clientIds): array
    {
        $catalog = [
            ['type' => 'phone', 'brand' => 'Apple', 'model' => 'iPhone 13'],
            ['type' => 'phone', 'brand' => 'Apple', 'model' => 'iPhone 14 Pro'],
            ['type' => 'phone', 'brand' => 'Samsung', 'model' => 'Galaxy S23'],
            ['type' => 'phone', 'brand' => 'Samsung', 'model' => 'Galaxy S25 Ultra'],
            ['type' => 'phone', 'brand' => 'Xiaomi', 'model' => 'Redmi Note 14 Pro'],
            ['type' => 'phone', 'brand' => 'Google', 'model' => 'Pixel 8'],
            ['type' => 'phone', 'brand' => 'OnePlus', 'model' => '12'],
            ['type' => 'laptop', 'brand' => 'ASUS', 'model' => 'ZenBook 14'],
            ['type' => 'laptop', 'brand' => 'Lenovo', 'model' => 'ThinkPad T14'],
            ['type' => 'laptop', 'brand' => 'HP', 'model' => 'Pavilion 15'],
            ['type' => 'laptop', 'brand' => 'Dell', 'model' => 'XPS 13'],
            ['type' => 'laptop', 'brand' => 'Apple', 'model' => 'MacBook Air M2'],
            ['type' => 'laptop', 'brand' => 'Acer', 'model' => 'Aspire 5'],
            ['type' => 'tablet', 'brand' => 'Apple', 'model' => 'iPad Air'],
            ['type' => 'tablet', 'brand' => 'Samsung', 'model' => 'Galaxy Tab S9'],
            ['type' => 'tablet', 'brand' => 'Lenovo', 'model' => 'Tab P12'],
            ['type' => 'tablet', 'brand' => 'Xiaomi', 'model' => 'Pad 6'],
            ['type' => 'tablet', 'brand' => 'Apple', 'model' => 'iPad Pro 11'],
        ];

        $devicesByClient = [];
        $deviceIndex = 0;
        $now = now();

        foreach ($clientIds as $clientIndex => $clientId) {
            $count = $clientIndex % 3 === 0 ? 3 : ($clientIndex % 2 === 0 ? 2 : 1);
            $devicesByClient[$clientId] = [];

            for ($i = 0; $i < $count; $i++) {
                $device = $catalog[$deviceIndex % count($catalog)];
                $payload = [
                    'user_id' => $clientId,
                    'type' => $device['type'],
                    'brand' => $device['brand'],
                    'model' => $device['model'],
                    'serial_number' => null,
                    'warranty_until' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (Schema::hasColumn('devices', 'component_type')) {
                    $payload['component_type'] = null;
                }

                if (Schema::hasColumn('devices', 'specs')) {
                    $payload['specs'] = null;
                }

                $id = (int) DB::table('devices')->insertGetId($payload);
                $devicesByClient[$clientId][] = $id;
                $deviceIndex++;
                $this->deviceCount++;
            }
        }

        return $devicesByClient;
    }

    private function createOrders(array $users, array $devices, array $branchIds, array $services, array $parts): array
    {
        $statuses = [
            'new',
            'new',
            'new',
            'new',
            'confirmed',
            'confirmed',
            'diagnostics',
            'diagnostics',
            'diagnostics',
            'in_progress',
            'in_progress',
            'in_progress',
            'in_progress',
            'in_progress',
            'waiting_parts',
            'waiting_parts',
            'ready',
            'done',
            'done',
            'done',
            'done',
            'done',
            'done',
            'done',
            'done',
            'done',
            'done',
            'done',
            'done',
            'cancelled',
        ];

        $presentationPlan = [
            0 => ['client_id' => $users['presentationClient'], 'status' => 'new', 'staff_id' => null, 'request_type' => 'general'],
            9 => ['client_id' => $users['presentationClient'], 'status' => 'in_progress', 'staff_id' => $users['presentationStaff'], 'request_type' => 'screen_battery'],
            16 => ['client_id' => $users['presentationClient'], 'status' => 'ready', 'staff_id' => $users['presentationStaff'], 'request_type' => 'screen_battery'],
            17 => ['client_id' => $users['presentationClient'], 'status' => 'done', 'staff_id' => $users['presentationStaff'], 'request_type' => 'quick_diagnostics'],
        ];

        $problems = [
            'Neieslēdzas pēc uzlādes.',
            'Ātri izlādējas akumulators.',
            'Saplaisājis ekrāns.',
            'Portatīvais dators pārkarst.',
            'USB-C ligzda slikti lādē.',
            'Pēc mitruma ierīce nedarbojas.',
            'Nepieciešama datu atjaunošana.',
            'Planšete reaģē ļoti lēni.',
            'Dators pats izslēdzas slodzes laikā.',
            'Ekrānā parādās svītras.',
        ];

        $orders = [];
        $clientIds = array_values(array_filter(
            $users['clients'],
            fn (int $clientId) => $clientId !== $users['presentationClient']
        ));

        foreach ($statuses as $index => $status) {
            $plan = $presentationPlan[$index] ?? null;
            $clientId = $plan['client_id'] ?? $clientIds[$index % count($clientIds)];
            $assignedStaffId = $plan['staff_id'] ?? ($status === 'new' || ($status === 'cancelled' && $index % 2 === 1) ? null : $users['staff'][$index % count($users['staff'])]);
            $createdAt = $this->demoDate($index);
            $deviceId = $devices[$clientId][$index % count($devices[$clientId])];
            $requestType = $plan['request_type'] ?? ['general', 'screen_battery', 'quick_diagnostics'][$index % 3];

            $items = $this->itemsForOrder($requestType, $index, $services, $parts);
            $finalCost = array_sum(array_column($items, 'line_total'));

            $orderPayload = [
                'order_number' => sprintf('DL-%s-%04d', $createdAt->format('Ymd'), $index + 1),
                'user_id' => $clientId,
                'branch_id' => $branchIds[$index % count($branchIds)],
                'device_id' => $deviceId,
                'status' => $status,
                'problem_description' => $problems[$index % count($problems)],
                'diagnosis' => in_array($status, ['diagnostics', 'in_progress', 'waiting_parts', 'ready', 'done'], true)
                    ? 'Veikta sākotnējā pārbaude un sagatavots remonta plāns.'
                    : null,
                'work_log' => in_array($status, ['in_progress', 'waiting_parts', 'ready', 'done'], true)
                    ? 'Darba process dokumentēts demonstrācijas datiem.'
                    : null,
                'estimated_cost' => $finalCost,
                'final_cost' => $finalCost,
                'started_at' => $assignedStaffId ? $createdAt->copy()->addDay() : null,
                'finished_at' => in_array($status, ['done', 'cancelled'], true) ? $createdAt->copy()->addDays(5) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays($this->statusSteps($status) - 1),
            ];

            if (Schema::hasColumn('orders', 'assigned_staff_id')) {
                $orderPayload['assigned_staff_id'] = $assignedStaffId;
            }

            if (Schema::hasColumn('orders', 'request_type')) {
                $orderPayload['request_type'] = $requestType;
            }

            $orderId = (int) DB::table('orders')->insertGetId($orderPayload);

            foreach ($items as $item) {
                DB::table('order_items')->insert([
                    ...$item,
                    'order_id' => $orderId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $this->createTimeline($orderId, $clientId, $assignedStaffId, $status, $createdAt);

            if ($status === 'done') {
                $this->createPayment($orderId, $clientId, $finalCost, $createdAt->copy()->addDays(5)->setTime(14, 20));
            }

            $orders[] = [
                'id' => $orderId,
                'user_id' => $clientId,
                'branch_id' => $orderPayload['branch_id'],
                'assigned_staff_id' => $assignedStaffId,
                'status' => $status,
                'final_cost' => $finalCost,
                'created_at' => $createdAt,
                'order_number' => $orderPayload['order_number'],
            ];
        }

        return $orders;
    }

    private function demoDate(int $index): Carbon
    {
        $base = Carbon::now()->subDays(58 - ($index * 2));
        $times = [[9, 17], [10, 43], [13, 28], [15, 52], [17, 6], [11, 35], [14, 12]];
        [$hour, $minute] = $times[$index % count($times)];

        return $base->setTime($hour, $minute, 0);
    }

    private function itemsForOrder(string $requestType, int $index, array $services, array $parts): array
    {
        $itemSets = [
            [
                ['item_type' => 'service', 'service_id' => $services['Diagnostika'], 'part_id' => null, 'quantity' => 1, 'unit_price' => 15.00],
            ],
            [
                ['item_type' => 'service', 'service_id' => $services['Akumulatora maiņa'], 'part_id' => null, 'quantity' => 1, 'unit_price' => 35.00],
                ['item_type' => 'part', 'service_id' => null, 'part_id' => $parts['Samsung akumulators'], 'quantity' => 1, 'unit_price' => 39.00],
            ],
            [
                ['item_type' => 'service', 'service_id' => $services['Ekrāna maiņa'], 'part_id' => null, 'quantity' => 1, 'unit_price' => 45.00],
                ['item_type' => 'part', 'service_id' => null, 'part_id' => $parts['iPhone ekrāna modulis'], 'quantity' => 1, 'unit_price' => 89.00],
            ],
            [
                ['item_type' => 'service', 'service_id' => $services['Ligzdas maiņa'], 'part_id' => null, 'quantity' => 1, 'unit_price' => 40.00],
                ['item_type' => 'part', 'service_id' => null, 'part_id' => $parts['USB-C ligzda'], 'quantity' => 1, 'unit_price' => 18.00],
            ],
            [
                ['item_type' => 'service', 'service_id' => $services['Portatīvā datora apkope'], 'part_id' => null, 'quantity' => 1, 'unit_price' => 45.00],
                ['item_type' => 'part', 'service_id' => null, 'part_id' => $parts['Termopasta un tīrīšanas komplekts'], 'quantity' => 1, 'unit_price' => 12.00],
            ],
            [
                ['item_type' => 'service', 'service_id' => $services['Datu atjaunošana'], 'part_id' => null, 'quantity' => 1, 'unit_price' => 90.00],
                ['item_type' => 'part', 'service_id' => null, 'part_id' => $parts['SSD 512GB'], 'quantity' => 1, 'unit_price' => 55.00],
            ],
        ];

        $selected = $requestType === 'quick_diagnostics'
            ? [['item_type' => 'service', 'service_id' => $services['Ātrā diagnostika'], 'part_id' => null, 'quantity' => 1, 'unit_price' => 20.00]]
            : $itemSets[$index % count($itemSets)];

        return array_map(function (array $item): array {
            return [
                ...$item,
                'line_total' => round($item['quantity'] * $item['unit_price'], 2),
            ];
        }, $selected);
    }

    private function statusSteps(string $status): int
    {
        return count($this->timelineStatuses($status));
    }

    private function createTimeline(int $orderId, int $clientId, ?int $staffId, string $status, Carbon $createdAt): void
    {
        $statuses = $this->timelineStatuses($status);
        $previous = null;

        foreach ($statuses as $stepIndex => $newStatus) {
            $changedBy = match ($newStatus) {
                'new', 'cancelled', 'done' => $clientId,
                default => $staffId,
            };

            DB::table('order_status_histories')->insert([
                'order_id' => $orderId,
                'old_status' => $previous,
                'new_status' => $newStatus,
                'changed_by' => $changedBy,
                'comment' => $this->timelineComment($newStatus),
                'created_at' => $createdAt->copy()->addDays($stepIndex)->addMinutes(37 * $stepIndex),
                'updated_at' => $createdAt->copy()->addDays($stepIndex)->addMinutes(37 * $stepIndex),
            ]);

            $previous = $newStatus;
        }
    }

    private function timelineStatuses(string $status): array
    {
        return match ($status) {
            'new' => ['new'],
            'confirmed' => ['new', 'confirmed'],
            'diagnostics' => ['new', 'confirmed', 'diagnostics'],
            'in_progress' => ['new', 'confirmed', 'diagnostics', 'in_progress'],
            'waiting_parts' => ['new', 'confirmed', 'diagnostics', 'waiting_parts'],
            'ready' => ['new', 'confirmed', 'diagnostics', 'in_progress', 'ready'],
            'done' => ['new', 'confirmed', 'diagnostics', 'in_progress', 'ready', 'done'],
            'cancelled' => ['new', 'cancelled'],
            default => ['new'],
        };
    }

    private function timelineComment(string $status): string
    {
        return match ($status) {
            'new' => 'Pieteikums izveidots.',
            'confirmed' => 'Pasūtījums pieņemts darbā.',
            'diagnostics' => 'Uzsākta diagnostika.',
            'in_progress' => 'Remonts uzsākts.',
            'waiting_parts' => 'Nepieciešama detaļa, gaidām piegādi.',
            'ready' => 'Ierīce gatava saņemšanai.',
            'done' => 'Klients apmaksāja pasūtījumu. Pasūtījums pabeigts.',
            'cancelled' => 'Klients atcēla pieteikumu.',
            default => 'Statuss mainīts.',
        };
    }

    private function createPayment(int $orderId, int $userId, float $amount, Carbon $paidAt): void
    {
        DB::table('payments')->insert([
            'order_id' => $orderId,
            'user_id' => $userId,
            'amount' => $amount,
            'status' => 'paid',
            'paid_at' => $paidAt,
            'method' => 'demo',
            'created_at' => $paidAt,
            'updated_at' => $paidAt,
        ]);

        $this->paymentCount++;
    }

    private function createReviews(array $orders): void
    {
        $comments = [
            'Ātri un saprotami. Paldies!',
            'Viss tika izskaidrots pirms remonta.',
            'Labs serviss, ierīce darbojas.',
            'Remonts aizņēma nedaudz ilgāk, bet rezultāts labs.',
            'Ļoti ērta pieteikuma sistēma.',
            'Darbinieks visu paskaidroja ļoti skaidri.',
            'Cena bija saprotama un bez pārsteigumiem.',
            'Pieteikumu bija viegli noformēt.',
            'Komunikācija bija skaidra visā remonta laikā.',
            'Ierīci saņēmu tīru un darba kārtībā.',
            'Labs rezultāts par saprātīgu cenu.',
            'Noteikti izmantotu servisu vēlreiz.',
        ];

        $doneOrders = array_values(array_filter($orders, fn (array $order) => $order['status'] === 'done'));

        foreach (array_slice($doneOrders, 0, 12) as $index => $order) {
            $createdAt = Carbon::parse($order['created_at'])->addDays(6)->setTime(16, 10 + $index);

            DB::table('reviews')->insert([
                'order_id' => $order['id'],
                'user_id' => $order['user_id'],
                'branch_id' => $order['branch_id'],
                'staff_id' => $order['assigned_staff_id'],
                'rating' => $index === 3 ? 3 : ($index % 4 === 0 ? 4 : 5),
                'comment' => $comments[$index],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $this->reviewCount++;
        }
    }

    private function createNotifications(array $users, array $orders): void
    {
        $presentationOrders = array_values(array_filter(
            $orders,
            fn (array $order) => $order['user_id'] === $users['presentationClient']
        ));

        foreach (array_slice($presentationOrders, 0, 5) as $index => $order) {
            $this->insertNotification(
                $users['presentationClient'],
                $order['id'],
                ['Pasūtījums pieņemts', 'Statuss mainīts', 'Pasūtījums gatavs'][$index % 3],
                ['Jūsu pasūtījumu pieņēma darbinieks.', 'Pasūtījuma statuss ir atjaunots.', 'Jūsu ierīce ir gatava saņemšanai.'][$index % 3],
                $index < 2
            );
        }

        foreach (array_slice($orders, 17, 5) as $index => $order) {
            $this->insertNotification(
                $users['presentationStaff'],
                $order['id'],
                ['Pasūtījums apmaksāts', 'Jauna atsauksme'][$index % 2],
                ['Klients apmaksāja pasūtījumu.', 'Klients atstāja atsauksmi par pasūtījumu.'][$index % 2],
                $index < 2
            );
        }

        foreach (array_slice($orders, 0, 3) as $index => $order) {
            $this->insertNotification(
                $users['admin'],
                $order['id'],
                'Jauns pasūtījums',
                'Sistēmā izveidots jauns demonstrācijas pasūtījums.',
                $index === 0
            );
        }
    }

    private function insertNotification(int $userId, int $orderId, string $title, string $message, bool $unread): void
    {
        DB::table('user_notifications')->insert([
            'user_id' => $userId,
            'order_id' => $orderId,
            'type' => 'demo',
            'title' => $title,
            'message' => $message,
            'data' => json_encode(['source' => 'presentation_demo']),
            'read_at' => $unread ? null : now()->subDays(1),
            'created_at' => now()->subHours(rand(1, 72)),
            'updated_at' => now()->subHours(rand(1, 72)),
        ]);
    }

    private function createContactMessages(): void
    {
        $messages = [
            ['name' => 'Ilze Priede', 'email' => 'ilze@example.com', 'phone' => '+371 21110001', 'message' => 'Vēlos noskaidrot aptuveno ekrāna maiņas cenu.'],
            ['name' => 'Jānis Balodis', 'email' => 'janis@example.com', 'phone' => '+371 21110002', 'message' => 'Portatīvais dators ļoti skaļi strādā un pārkarst.'],
            ['name' => 'Eva Liepiņa', 'email' => 'eva@example.com', 'phone' => '+371 21110003', 'message' => 'Vai varat palīdzēt ar datu atjaunošanu no SSD?'],
            ['name' => 'Mārtiņš Kalns', 'email' => null, 'phone' => '+371 21110004', 'message' => 'Nepieciešama planšetes diagnostika.'],
        ];

        foreach ($messages as $message) {
            DB::table('contact_messages')->insert([
                ...$message,
                'status' => 'new',
                'created_at' => now()->subDays(rand(1, 12)),
                'updated_at' => now()->subDays(rand(1, 12)),
            ]);
        }
    }
}

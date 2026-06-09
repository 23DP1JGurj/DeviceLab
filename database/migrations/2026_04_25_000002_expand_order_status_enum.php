<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE orders
            MODIFY status ENUM(
                'new',
                'confirmed',
                'diagnostics',
                'in_progress',
                'waiting_parts',
                'ready',
                'done',
                'cancelled'
            ) NOT NULL DEFAULT 'new'
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE orders
            MODIFY status ENUM(
                'new',
                'confirmed',
                'in_progress',
                'waiting_parts',
                'done',
                'cancelled'
            ) NOT NULL DEFAULT 'new'
        ");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'request_type')) {
                $table->string('request_type')->nullable()->after('status');
            }
        });

        Schema::table('devices', function (Blueprint $table) {
            if (! Schema::hasColumn('devices', 'component_type')) {
                $table->string('component_type')->nullable()->after('type');
            }

            if (! Schema::hasColumn('devices', 'specs')) {
                $table->text('specs')->nullable()->after('model');
            }
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasColumn('devices', 'specs')) {
                $table->dropColumn('specs');
            }

            if (Schema::hasColumn('devices', 'component_type')) {
                $table->dropColumn('component_type');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'request_type')) {
                $table->dropColumn('request_type');
            }
        });
    }
};

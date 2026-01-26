<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('devices', function (Blueprint $table) {
        $table->id();

        // Владелец устройства (пока используем users как клиентов)
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();

        $table->string('type');         // phone / laptop / pc / etc
        $table->string('brand')->nullable();
        $table->string('model')->nullable();
        $table->string('serial_number')->nullable();
        $table->date('warranty_until')->nullable();

        $table->timestamps();

        $table->index(['user_id', 'type']);
    });
}

public function down(): void
{
    Schema::dropIfExists('devices');
}

};

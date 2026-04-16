<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_model_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('device_type');
            $table->string('brand');
            $table->string('model');
            $table->unsignedInteger('popularity')->default(0);
            $table->string('source')->nullable();
            $table->timestamps();

            $table->unique(['device_type', 'brand', 'model'], 'device_model_suggestions_unique');
            $table->index(['device_type', 'brand']);
            $table->index('model');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_model_suggestions');
    }
};

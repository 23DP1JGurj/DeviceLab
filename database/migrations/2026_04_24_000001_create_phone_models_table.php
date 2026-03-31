<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_models', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('model');
            $table->timestamps();

            $table->unique(['brand', 'model']);
            $table->index('brand');
            $table->index('model');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_models');
    }
};

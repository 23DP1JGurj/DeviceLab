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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();

        $table->string('order_number')->unique(); // напр: DL-20260126-0001

        $table->foreignId('user_id')->constrained()->cascadeOnDelete();          // клиент
        $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
        $table->foreignId('device_id')->constrained('devices')->restrictOnDelete();

        $table->enum('status', ['new', 'confirmed', 'in_progress', 'waiting_parts', 'done', 'cancelled'])
              ->default('new');

        $table->text('problem_description')->nullable();
        $table->text('diagnosis')->nullable();
        $table->text('work_log')->nullable();

        $table->decimal('estimated_cost', 10, 2)->nullable();
        $table->decimal('final_cost', 10, 2)->nullable();

        $table->timestamp('started_at')->nullable();
        $table->timestamp('finished_at')->nullable();

        $table->timestamps();

        $table->index(['status', 'branch_id']);
        $table->index(['user_id', 'created_at']);
    });
}

public function down(): void
{
    Schema::dropIfExists('orders');
}

};

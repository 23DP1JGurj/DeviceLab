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
    Schema::create('order_items', function (Blueprint $table) {
        $table->id();

        $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

        // Один item = либо услуга, либо деталь
        $table->enum('item_type', ['service', 'part']);

        $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
        $table->foreignId('part_id')->nullable()->constrained('parts')->nullOnDelete();

        $table->unsignedInteger('quantity')->default(1);
        $table->decimal('unit_price', 10, 2)->default(0);
        $table->decimal('line_total', 10, 2)->default(0);

        $table->timestamps();

        $table->index(['order_id', 'item_type']);
    });
}

public function down(): void
{
    Schema::dropIfExists('order_items');
}

};

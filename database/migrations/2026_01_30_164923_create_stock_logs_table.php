<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            // add = manual stock add, deduct = order deduction, adjust = correction
            $table->enum('type', ['add', 'deduct', 'adjust']);

            $table->decimal('qty', 12, 3);
            $table->decimal('before_stock', 12, 3);
            $table->decimal('after_stock', 12, 3);

            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->string('reason')->nullable(); // "Manual stock add", "Order placed", etc.

            $table->timestamps();

            $table->index(['material_id', 'type']);
            $table->index(['order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};

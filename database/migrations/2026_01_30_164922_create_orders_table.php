<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_no')->unique(); // e.g., ORD-2026-000001
            $table->foreignId('placed_by')->constrained('users')->restrictOnDelete();

            $table->enum('status', ['Received', 'In Progress', 'Completed', 'Dispatched'])->default('Received');

            // For auditing, store when placed (also have timestamps)
            $table->timestamp('placed_at')->useCurrent();

            // Optional notes
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['status', 'placed_at']);
            $table->index(['placed_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

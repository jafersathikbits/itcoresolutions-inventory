<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();

            // Who should see this notification (Admin/Super Admin users)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // low_stock_warning, order_blocked, order_warning, report_generated, etc.
            $table->string('type');

            $table->string('title');
            $table->text('message')->nullable();

            // extra payload (order_id, material_ids, etc.)
            $table->json('data')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};

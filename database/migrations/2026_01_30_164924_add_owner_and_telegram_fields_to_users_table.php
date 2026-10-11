<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Owner = first Super Admin (cannot be deleted until ownership transfer)
            $table->boolean('is_owner')->default(false)->after('remember_token');

            // Telegram target (user chat_id or group chat_id)
            $table->string('telegram_chat_id')->nullable()->after('is_owner');

            // Optional: store if this chat_id is a group
            $table->boolean('telegram_is_group')->default(false)->after('telegram_chat_id');

            // Track ownership transfers
            $table->timestamp('ownership_transferred_at')->nullable()->after('telegram_is_group');

            $table->index(['is_owner']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_owner']);
            $table->dropColumn([
                'is_owner',
                'telegram_chat_id',
                'telegram_is_group',
                'ownership_transferred_at',
            ]);
        });
    }
};

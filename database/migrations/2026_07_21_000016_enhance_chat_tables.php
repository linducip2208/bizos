<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            if (!Schema::hasColumn('chats', 'is_channel')) {
                $table->boolean('is_channel')->default(false)->after('chat_type');
            }
            if (!Schema::hasColumn('chats', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('chats', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();
            }
        });

        Schema::create('chat_message_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
        });

        Schema::create('chat_typing_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->timestamp('last_typed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_typing_indicators');
        Schema::dropIfExists('chat_message_mentions');

        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['is_channel', 'description']);
        });
    }
};

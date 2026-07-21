<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->foreignId('flow_id')->nullable()->after('assigned_to')->constrained('chatbot_flows')->nullOnDelete();
            $table->json('flow_state')->nullable()->after('flow_id');
            $table->string('chatbot_intent', 100)->nullable()->after('flow_state');
            $table->decimal('chatbot_confidence', 5, 2)->nullable()->after('chatbot_intent');
            $table->timestamp('last_bot_message_at')->nullable()->after('last_message_at');
            $table->boolean('is_bot_active')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->dropForeign(['flow_id']);
            $table->dropColumn([
                'flow_id',
                'flow_state',
                'chatbot_intent',
                'chatbot_confidence',
                'last_bot_message_at',
                'is_bot_active',
            ]);
        });
    }
};

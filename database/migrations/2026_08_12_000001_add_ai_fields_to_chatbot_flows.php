<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_flows', function (Blueprint $table) {
            $table->boolean('is_ai_powered')->default(false)->after('fallback_message');
            $table->foreignId('ai_provider_id')->nullable()->after('is_ai_powered')->constrained('ai_providers')->nullOnDelete();
            $table->text('ai_prompt_template')->nullable()->after('ai_provider_id');
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_flows', function (Blueprint $table) {
            $table->dropForeign(['ai_provider_id']);
            $table->dropColumn(['is_ai_powered', 'ai_provider_id', 'ai_prompt_template']);
        });
    }
};

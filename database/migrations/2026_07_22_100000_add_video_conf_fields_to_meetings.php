<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('provider', 50)->nullable()->after('meeting_type')->comment('zoom / google_meet / internal');
            $table->string('provider_meeting_id', 255)->nullable()->after('provider');
            $table->string('meeting_url', 500)->nullable()->after('provider_meeting_id');
            $table->string('passcode', 50)->nullable()->after('meeting_url');
            $table->string('dial_in', 255)->nullable()->after('passcode');
            $table->string('recording_path', 500)->nullable()->after('dial_in');
            $table->longText('transcript_text')->nullable()->after('recording_path');
            $table->json('ai_summary')->nullable()->after('transcript_text');
            $table->boolean('is_recurring')->default(false)->after('ai_summary');
            $table->string('recurrence_frequency', 20)->nullable()->after('is_recurring')->comment('daily / weekly / monthly');
            $table->timestamp('recurrence_until')->nullable()->after('recurrence_frequency');
            $table->foreignId('linked_project_id')->nullable()->after('recurrence_until')->constrained('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign(['linked_project_id']);
            $table->dropColumn([
                'provider', 'provider_meeting_id', 'meeting_url', 'passcode',
                'dial_in', 'recording_path', 'transcript_text', 'ai_summary',
                'is_recurring', 'recurrence_frequency', 'recurrence_until', 'linked_project_id',
            ]);
        });
    }
};

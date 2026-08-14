<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->string('external_id', 255)->nullable()->after('eventable_id');
            $table->string('external_provider', 30)->nullable()->after('external_id');
            $table->timestamp('last_synced_at')->nullable()->after('external_provider');

            $table->index(['external_provider', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropIndex(['external_provider', 'external_id']);
            $table->dropColumn(['external_id', 'external_provider', 'last_synced_at']);
        });
    }
};

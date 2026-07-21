<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('workflows', 'workflow_type')) {
                $table->string('workflow_type', 30)->default('simple')->after('description');
            }
            if (!Schema::hasColumn('workflows', 'studio_config')) {
                $table->json('studio_config')->nullable()->after('actions');
            }
            if (!Schema::hasColumn('workflows', 'enabled_blocks')) {
                $table->json('enabled_blocks')->nullable()->after('studio_config');
            }
            if (!Schema::hasColumn('workflows', 'webhook_url')) {
                $table->string('webhook_url', 200)->nullable()->after('enabled_blocks');
            }
            if (!Schema::hasColumn('workflows', 'schedule_cron')) {
                $table->string('schedule_cron', 100)->nullable()->after('webhook_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn([
                'workflow_type',
                'studio_config',
                'enabled_blocks',
                'webhook_url',
                'schedule_cron',
            ]);
        });
    }
};

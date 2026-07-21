<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'action_type')) {
                $table->string('action_type', 50)->nullable()->after('action');
            }

            if (!Schema::hasColumn('audit_logs', 'restorable')) {
                $table->boolean('restorable')->default(false)->after('user_agent');
            }

            if (!Schema::hasColumn('audit_logs', 'metadata')) {
                $table->json('metadata')->nullable()->after('restorable');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['action_type', 'restorable', 'metadata']);
        });
    }
};

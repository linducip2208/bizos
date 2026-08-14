<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('report_templates', 'report_type')) {
                $table->string('report_type')->default('standard')->after('query_type');
            }

            if (!Schema::hasColumn('report_templates', 'pivot_config')) {
                $table->json('pivot_config')->nullable()->after('report_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_templates', function (Blueprint $table) {
            if (Schema::hasColumn('report_templates', 'pivot_config')) {
                $table->dropColumn('pivot_config');
            }

            if (Schema::hasColumn('report_templates', 'report_type')) {
                $table->dropColumn('report_type');
            }
        });
    }
};

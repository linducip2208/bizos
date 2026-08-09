<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coa', function (Blueprint $table) {
            if (!Schema::hasColumn('coa', 'cost_center_id')) {
                $table->foreignId('cost_center_id')->nullable()->after('is_active')->constrained('cost_centers')->nullOnDelete();
            }
            if (!Schema::hasColumn('coa', 'profit_center_id')) {
                $table->foreignId('profit_center_id')->nullable()->after('cost_center_id')->constrained('profit_centers')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('coa', function (Blueprint $table) {
            $table->dropForeign(['profit_center_id']);
            $table->dropColumn('profit_center_id');
            $table->dropForeign(['cost_center_id']);
            $table->dropColumn('cost_center_id');
        });
    }
};

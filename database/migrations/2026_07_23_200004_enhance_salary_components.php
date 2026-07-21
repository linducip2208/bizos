<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_components', function (Blueprint $table) {
            $table->enum('component_category', ['basic', 'allowance', 'deduction', 'bonus', 'overtime', 'commission'])
                ->nullable()
                ->after('name');
        });

        DB::statement("ALTER TABLE salary_components MODIFY COLUMN calculation_type ENUM('fixed', 'percentage', 'formula', 'per_day', 'per_hour', 'per_attendance', 'variable') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE salary_components MODIFY COLUMN calculation_type ENUM('fixed', 'percentage', 'formula', 'per_day', 'per_hour', 'per_attendance') NOT NULL");

        Schema::table('salary_components', function (Blueprint $table) {
            $table->dropColumn('component_category');
        });
    }
};

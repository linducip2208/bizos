<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pph21_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('pph21_configs', 'ptkp_values_json')) {
                $table->json('ptkp_values_json')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('pph21_configs', 'ter_category_a_rates_json')) {
                $table->json('ter_category_a_rates_json')->nullable()->after('ptkp_values_json');
            }
            if (!Schema::hasColumn('pph21_configs', 'ter_category_b_rates_json')) {
                $table->json('ter_category_b_rates_json')->nullable()->after('ter_category_a_rates_json');
            }
            if (!Schema::hasColumn('pph21_configs', 'ter_category_c_rates_json')) {
                $table->json('ter_category_c_rates_json')->nullable()->after('ter_category_b_rates_json');
            }
            if (!Schema::hasColumn('pph21_configs', 'ter_year')) {
                $table->integer('ter_year')->nullable()->after('ter_category_c_rates_json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pph21_configs', function (Blueprint $table) {
            $table->dropColumn([
                'ptkp_values_json',
                'ter_category_a_rates_json',
                'ter_category_b_rates_json',
                'ter_category_c_rates_json',
                'ter_year',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('ptkp_code', 10)->nullable()->after('tax_number')->comment('TK/0, TK/1, TK/2, TK/3, K/0, K/1, K/2, K/3');
            $table->string('bpjs_kes_tier', 5)->nullable()->after('bpjs_kesehatan')->comment('I, II, III');
            $table->string('bpjs_tk_risk_grade', 20)->nullable()->after('bpjs_ketenagakerjaan')->comment('very_low, low, medium, high, very_high');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['ptkp_code', 'bpjs_kes_tier', 'bpjs_tk_risk_grade']);
        });
    }
};

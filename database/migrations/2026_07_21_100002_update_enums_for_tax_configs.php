<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pph21_configs MODIFY COLUMN ptkp_category ENUM('tk0','tk1','tk2','tk3','k0','k1','k2','k3','default') NOT NULL DEFAULT 'default'");

        DB::statement("ALTER TABLE bpjs_configs MODIFY COLUMN bpjs_type ENUM('tk_jht','tk_jp','tk_jkk','tk_jkm','kes','kesehatan','jkk_very_low','jkk_low','jkk_medium','jkk_high','jkk_very_high','jkm','jht','jp') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pph21_configs MODIFY COLUMN ptkp_category ENUM('tk0','tk1','tk2','tk3','k0','k1','k2','k3') NOT NULL");

        DB::statement("ALTER TABLE bpjs_configs MODIFY COLUMN bpjs_type ENUM('tk_jht','tk_jp','tk_jkk','tk_jkm','kes') NOT NULL");
    }
};

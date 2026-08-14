<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->string('statement_file_path')->nullable()->after('notes');
            $table->integer('auto_matched_count')->default(0)->after('statement_file_path');
            $table->integer('manual_matched_count')->default(0)->after('auto_matched_count');
            $table->integer('unmatched_count')->default(0)->after('manual_matched_count');
        });
    }

    public function down(): void
    {
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->dropColumn(['statement_file_path', 'auto_matched_count', 'manual_matched_count', 'unmatched_count']);
        });
    }
};

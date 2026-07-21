<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waste_records', function (Blueprint $table) {
            $table->unsignedBigInteger('production_waste_log_id')->nullable()->after('source');
            $table->foreign('production_waste_log_id')->references('id')->on('waste_logs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('waste_records', function (Blueprint $table) {
            $table->dropForeign(['production_waste_log_id']);
            $table->dropColumn('production_waste_log_id');
        });
    }
};

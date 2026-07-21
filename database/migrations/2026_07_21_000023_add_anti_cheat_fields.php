<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->integer('violation_count')->default(0)->after('attempt_number');
            $table->json('violation_log')->nullable()->after('violation_count');
            $table->boolean('is_auto_submitted')->default(false)->after('violation_log');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['violation_count', 'violation_log', 'is_auto_submitted']);
        });
    }
};

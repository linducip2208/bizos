<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback_cycles', function (Blueprint $table) {
            $table->foreignId('performance_review_id')->nullable()->after('company_id')
                ->constrained('performance_reviews')->nullOnDelete()
                ->comment('Integrasi dengan performance review untuk 360 feedback');
        });
    }

    public function down(): void
    {
        Schema::table('feedback_cycles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('performance_review_id');
        });
    }
};

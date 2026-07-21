<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('is_sandbox')->default(false)->after('subscription_end');
            $table->foreignId('sandbox_source_id')->nullable()->after('is_sandbox')->constrained('companies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['sandbox_source_id']);
            $table->dropColumn(['is_sandbox', 'sandbox_source_id']);
        });
    }
};

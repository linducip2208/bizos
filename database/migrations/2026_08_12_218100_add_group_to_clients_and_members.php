<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('customer_group_id')->nullable()->after('status')->constrained('customer_groups')->nullOnDelete();
        });

        Schema::table('pos_members', function (Blueprint $table) {
            $table->foreignId('customer_group_id')->nullable()->after('is_active')->constrained('customer_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_group_id');
        });

        Schema::table('pos_members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_group_id');
        });
    }
};

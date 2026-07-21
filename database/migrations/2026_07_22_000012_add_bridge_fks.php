<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $table->foreignId('pos_refund_id')->nullable()->after('pos_transaction_id')->constrained('pos_refunds')->nullOnDelete();
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->foreignId('pos_transaction_id')->nullable()->after('status')->constrained('pos_transactions')->nullOnDelete();
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('enrollment_id')->constrained('employees')->nullOnDelete();
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('client_id')->constrained('projects')->nullOnDelete();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('deal_id')->nullable()->after('client_id')->constrained('deals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pos_refund_id');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pos_transaction_id');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deal_id');
        });
    }
};

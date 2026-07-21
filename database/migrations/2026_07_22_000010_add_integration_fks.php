<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->foreignId('journal_id')->nullable()->after('notes')->constrained('journals')->nullOnDelete();
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('notes')->constrained('invoices')->nullOnDelete();
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('notes')->constrained('invoices')->nullOnDelete();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('terms_conditions')->constrained('invoices')->nullOnDelete();
        });

        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->foreignId('journal_id')->nullable()->after('total_employees')->constrained('journals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropForeign(['journal_id']);
            $table->dropColumn('journal_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });

        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropForeign(['journal_id']);
            $table->dropColumn('journal_id');
        });
    }
};

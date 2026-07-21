<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_order_id')->nullable()->after('reference_id');
            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->nullOnDelete();
        });

        DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_type ENUM('sales', 'purchase', 'credit_note', 'debit_note', 'service', 'other') NOT NULL DEFAULT 'sales'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_type ENUM('sales', 'purchase', 'credit_note', 'debit_note') NOT NULL");

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['sales_order_id']);
            $table->dropColumn('sales_order_id');
        });
    }
};

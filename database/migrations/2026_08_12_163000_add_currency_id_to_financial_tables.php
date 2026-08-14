<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'currency_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->nullable();
            });
        }

        if (Schema::hasTable('journals') && !Schema::hasColumn('journals', 'currency_id')) {
            Schema::table('journals', function (Blueprint $table) {
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->nullable();
            });
        }

        if (Schema::hasTable('payments') && !Schema::hasColumn('payments', 'currency_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->nullable();
            });
        }

        if (Schema::hasTable('sales_orders') && !Schema::hasColumn('sales_orders', 'currency_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->nullable();
            });
        }

        if (Schema::hasTable('purchase_orders') && !Schema::hasColumn('purchase_orders', 'currency_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->nullable();
            });
        }

        if (Schema::hasTable('journal_entries') && !Schema::hasColumn('journal_entries', 'currency_id')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->nullable();
            });
        }
    }

    public function down(): void
    {
        $tables = ['invoices', 'journals', 'payments', 'sales_orders', 'purchase_orders', 'journal_entries'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'exchange_rate')) {
                        $table->dropColumn('exchange_rate');
                    }
                });

                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'currency_id')) {
                        $table->dropForeign(['currency_id']);
                        $table->dropColumn('currency_id');
                    }
                });
            }
        }
    }
};

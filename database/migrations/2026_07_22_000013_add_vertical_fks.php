<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ================================================================
        // 1. KONSTRUKSI → PROJECT + FINANCE
        // ================================================================

        // progress_billings: invoice_id FK (progress billing → invoice)
        if (!Schema::hasColumn('progress_billings', 'invoice_id')) {
            Schema::table('progress_billings', function (Blueprint $table) {
                $table->foreignId('invoice_id')->nullable()->after('status')
                    ->constrained('invoices')->nullOnDelete();
            });
        }

        // daily_site_reports: worker_list untuk auto-attendance
        // (kolom worker_list sudah ada sebagai worker_count, gunakan itu)

        // subcontractor_contracts: supplier_id (sudah ada, pastikan FK)
        if (Schema::hasColumn('subcontractor_contracts', 'supplier_id')) {
            Schema::table('subcontractor_contracts', function (Blueprint $table) {
                if (!$this->hasForeignKey('subcontractor_contracts', 'subcontractor_contracts_supplier_id_foreign')) {
                    $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
                }
            });
        }

        // ================================================================
        // 2. PERHOTELAN → POS + FINANCE
        // ================================================================

        // guest_folios: add invoice_id FK, payment_id FK
        Schema::table('guest_folios', function (Blueprint $table) {
            if (!Schema::hasColumn('guest_folios', 'invoice_id')) {
                $table->foreignId('invoice_id')->nullable()->after('payment_status')
                    ->constrained('invoices')->nullOnDelete();
            }
            if (!Schema::hasColumn('guest_folios', 'payment_id')) {
                $table->foreignId('payment_id')->nullable()->after('invoice_id')
                    ->constrained('payments')->nullOnDelete();
            }
        });

        // room_bookings: add client_id FK (nullable)
        if (!Schema::hasColumn('room_bookings', 'client_id')) {
            Schema::table('room_bookings', function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->after('company_id')
                    ->constrained('clients')->nullOnDelete();
            });
        }

        // ================================================================
        // 3. PROPERTI → FINANCE + CRM
        // ================================================================

        // service_charge_invoices: add finance_invoice_id FK
        if (!Schema::hasColumn('service_charge_invoices', 'finance_invoice_id')) {
            Schema::table('service_charge_invoices', function (Blueprint $table) {
                $table->foreignId('finance_invoice_id')->nullable()->after('status')
                    ->constrained('invoices')->nullOnDelete();
            });
        }

        // maintenance_requests: add work_order_id FK
        if (!Schema::hasColumn('maintenance_requests', 'work_order_id')) {
            Schema::table('maintenance_requests', function (Blueprint $table) {
                $table->foreignId('work_order_id')->nullable()->after('status')
                    ->constrained('work_orders')->nullOnDelete();
            });
        }

        // tenancy_contracts: client_id (sudah ada, pastikan FK)
        if (Schema::hasColumn('tenancy_contracts', 'client_id')) {
            Schema::table('tenancy_contracts', function (Blueprint $table) {
                if (!$this->hasForeignKey('tenancy_contracts', 'tenancy_contracts_client_id_foreign')) {
                    $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
                }
            });
        }

        // ================================================================
        // 4. LOGISTIK → POS + PROCUREMENT
        // ================================================================

        // delivery_orders: add pos_transaction_id FK
        if (!Schema::hasColumn('delivery_orders', 'pos_transaction_id')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                $table->foreignId('pos_transaction_id')->nullable()->after('invoice_id')
                    ->constrained('pos_transactions')->nullOnDelete();
            });
        }

        // ================================================================
        // 5. FIELD SERVICE → INVENTORY + FINANCE
        // ================================================================

        // work_orders: add invoice_id FK (field service)
        if (!Schema::hasColumn('work_orders', 'invoice_id')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->foreignId('invoice_id')->nullable()->after('total_cost')
                    ->constrained('invoices')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // progress_billings
        Schema::table('progress_billings', function (Blueprint $table) {
            if (Schema::hasColumn('progress_billings', 'invoice_id')) {
                $table->dropForeign(['invoice_id']);
                $table->dropColumn('invoice_id');
            }
        });

        // guest_folios
        Schema::table('guest_folios', function (Blueprint $table) {
            if (Schema::hasColumn('guest_folios', 'payment_id')) {
                $table->dropForeign(['payment_id']);
                $table->dropColumn('payment_id');
            }
            if (Schema::hasColumn('guest_folios', 'invoice_id')) {
                $table->dropForeign(['invoice_id']);
                $table->dropColumn('invoice_id');
            }
        });

        // room_bookings
        Schema::table('room_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('room_bookings', 'client_id')) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            }
        });

        // service_charge_invoices
        Schema::table('service_charge_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('service_charge_invoices', 'finance_invoice_id')) {
                $table->dropForeign(['finance_invoice_id']);
                $table->dropColumn('finance_invoice_id');
            }
        });

        // maintenance_requests
        Schema::table('maintenance_requests', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance_requests', 'work_order_id')) {
                $table->dropForeign(['work_order_id']);
                $table->dropColumn('work_order_id');
            }
        });

        // delivery_orders
        Schema::table('delivery_orders', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_orders', 'pos_transaction_id')) {
                $table->dropForeign(['pos_transaction_id']);
                $table->dropColumn('pos_transaction_id');
            }
        });

        // work_orders
        Schema::table('work_orders', function (Blueprint $table) {
            if (Schema::hasColumn('work_orders', 'invoice_id')) {
                $table->dropForeign(['invoice_id']);
                $table->dropColumn('invoice_id');
            }
        });
    }

    /**
     * Check apakah foreign key sudah ada.
     */
    private function hasForeignKey(string $table, string $fkName): bool
    {
        $db = Schema::getConnection()->getDatabaseName();
        $result = DB::select(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$db, $table, $fkName]
        );
        return !empty($result);
    }
};

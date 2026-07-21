<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SystemReportSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding system report templates...');

        $companyId = DB::table('companies')->first()?->id ?? 1;
        $adminUserId = DB::table('users')->where('role_id', DB::table('roles')->where('slug', 'admin')->first()?->id)->first()?->id
            ?? DB::table('users')->first()?->id ?? 1;

        $templates = [
            [
                'name' => 'Revenue Summary',
                'slug' => 'revenue-summary',
                'description' => 'Ringkasan pendapatan dari invoice dan transaksi POS per periode.',
                'category' => 'sales',
                'query_type' => 'chart',
                'query_config' => [
                    'table_name' => 'invoices',
                    'select' => [
                        "DATE_FORMAT(invoice_date, '%Y-%m') as period",
                        'SUM(total) as total_revenue',
                        'COUNT(*) as transaction_count',
                        'AVG(total) as avg_per_transaction',
                    ],
                    'group_by' => ['period'],
                    'sort' => [['column' => 'period', 'direction' => 'asc']],
                ],
                'chart_config' => [
                    'type' => 'bar',
                    'x_axis' => 'period',
                    'y_axis' => 'total_revenue',
                    'colors' => ['#4f46e5', '#7c3aed', '#2563eb', '#059669', '#d97706'],
                ],
            ],
            [
                'name' => 'Profit & Loss',
                'slug' => 'profit-loss',
                'description' => 'Laporan laba rugi menampilkan pendapatan, beban, dan laba bersih per bulan.',
                'category' => 'finance',
                'query_type' => 'summary',
                'query_config' => [
                    'table_name' => 'journal_entries',
                    'joins' => [
                        [
                            'table' => 'journals',
                            'first' => 'journal_entries.journal_id',
                            'operator' => '=',
                            'second' => 'journals.id',
                            'type' => 'inner',
                        ],
                        [
                            'table' => 'coa',
                            'first' => 'journal_entries.coa_id',
                            'operator' => '=',
                            'second' => 'coa.id',
                            'type' => 'inner',
                        ],
                    ],
                    'select' => [
                        "DATE_FORMAT(journals.journal_date, '%Y-%m') as period",
                        'coa.name as account_name',
                        'coa.code as account_code',
                        'SUM(journal_entries.debit) as total_debit',
                        'SUM(journal_entries.credit) as total_credit',
                    ],
                    'group_by' => ['period', 'account_name', 'account_code'],
                    'sort' => [['column' => 'period', 'direction' => 'asc']],
                ],
                'chart_config' => [
                    'type' => 'bar',
                    'x_axis' => 'period',
                    'y_axis' => ['total_debit', 'total_credit'],
                    'colors' => ['#dc2626', '#059669'],
                ],
            ],
            [
                'name' => 'AR Aging',
                'slug' => 'ar-aging',
                'description' => 'Analisis umur piutang (Account Receivable) — overdue 0-30, 31-60, 61-90, >90 hari.',
                'category' => 'finance',
                'query_type' => 'chart',
                'query_config' => [
                    'table_name' => 'invoices',
                    'select' => [
                        "CASE
                            WHEN DATEDIFF(NOW(), due_date) <= 0 THEN 'Belum Jatuh Tempo'
                            WHEN DATEDIFF(NOW(), due_date) BETWEEN 1 AND 30 THEN '1-30 Hari'
                            WHEN DATEDIFF(NOW(), due_date) BETWEEN 31 AND 60 THEN '31-60 Hari'
                            WHEN DATEDIFF(NOW(), due_date) BETWEEN 61 AND 90 THEN '61-90 Hari'
                            ELSE '>90 Hari'
                        END as aging_bucket",
                        'SUM(remaining_amount) as total_outstanding',
                        'COUNT(*) as invoice_count',
                    ],
                    'group_by' => ['aging_bucket'],
                    'sort' => [['column' => 'aging_bucket', 'direction' => 'asc']],
                ],
                'chart_config' => [
                    'type' => 'pie',
                    'x_axis' => 'aging_bucket',
                    'y_axis' => 'total_outstanding',
                    'colors' => ['#059669', '#d97706', '#ea580c', '#dc2626', '#991b1b'],
                ],
            ],
            [
                'name' => 'AP Aging',
                'slug' => 'ap-aging',
                'description' => 'Analisis umur hutang (Account Payable) per kategori umur.',
                'category' => 'finance',
                'query_type' => 'table',
                'query_config' => [
                    'table_name' => 'invoices',
                    'select' => [
                        "CASE
                            WHEN DATEDIFF(NOW(), due_date) <= 0 THEN 'Belum Jatuh Tempo'
                            WHEN DATEDIFF(NOW(), due_date) BETWEEN 1 AND 30 THEN '1-30 Hari'
                            WHEN DATEDIFF(NOW(), due_date) BETWEEN 31 AND 60 THEN '31-60 Hari'
                            WHEN DATEDIFF(NOW(), due_date) BETWEEN 61 AND 90 THEN '61-90 Hari'
                            ELSE '>90 Hari'
                        END as aging_bucket",
                        'SUM(total) as total_payable',
                        'COUNT(*) as bill_count',
                    ],
                    'group_by' => ['aging_bucket'],
                    'sort' => [['column' => 'aging_bucket', 'direction' => 'asc']],
                ],
                'chart_config' => null,
            ],
            [
                'name' => 'Employee Headcount',
                'slug' => 'employee-headcount',
                'description' => 'Jumlah karyawan per department dan per status.',
                'category' => 'hrm',
                'query_type' => 'chart',
                'query_config' => [
                    'table_name' => 'employees',
                    'joins' => [
                        [
                            'table' => 'departments',
                            'first' => 'employees.department_id',
                            'operator' => '=',
                            'second' => 'departments.id',
                            'type' => 'left',
                        ],
                    ],
                    'select' => [
                        'departments.name as department',
                        'employees.status',
                        'COUNT(*) as total',
                    ],
                    'group_by' => ['department', 'status'],
                    'sort' => [['column' => 'department', 'direction' => 'asc']],
                ],
                'chart_config' => [
                    'type' => 'bar',
                    'x_axis' => 'department',
                    'y_axis' => 'total',
                    'colors' => ['#4f46e5', '#7c3aed', '#2563eb', '#059669', '#d97706'],
                ],
            ],
            [
                'name' => 'Attendance Summary',
                'slug' => 'attendance-summary',
                'description' => 'Ringkasan kehadiran karyawan — hadir, terlambat, absen, cuti per hari.',
                'category' => 'hrm',
                'query_type' => 'chart',
                'query_config' => [
                    'table_name' => 'attendances',
                    'select' => [
                        "DATE_FORMAT(date, '%Y-%m-%d') as day",
                        "SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as hadir",
                        "SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as terlambat",
                        "SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absen",
                        "SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) as cuti",
                    ],
                    'group_by' => ['day'],
                    'sort' => [['column' => 'day', 'direction' => 'asc']],
                ],
                'chart_config' => [
                    'type' => 'line',
                    'x_axis' => 'day',
                    'y_axis' => ['hadir', 'terlambat', 'absen', 'cuti'],
                    'colors' => ['#059669', '#d97706', '#dc2626', '#2563eb'],
                ],
            ],
            [
                'name' => 'Inventory Stock Level',
                'slug' => 'inventory-stock-level',
                'description' => 'Level stok produk per kategori — tersedia, rendah, habis.',
                'category' => 'inventory',
                'query_type' => 'table',
                'query_config' => [
                    'table_name' => 'products',
                    'joins' => [
                        [
                            'table' => 'product_categories',
                            'first' => 'products.category_id',
                            'operator' => '=',
                            'second' => 'product_categories.id',
                            'type' => 'left',
                        ],
                    ],
                    'select' => [
                        'products.name as product_name',
                        'product_categories.name as category',
                        'products.stock',
                        'products.min_stock',
                        "CASE
                            WHEN products.stock <= 0 THEN 'Habis'
                            WHEN products.stock <= products.min_stock THEN 'Rendah'
                            ELSE 'Tersedia'
                        END as stock_status",
                    ],
                    'sort' => [['column' => 'category', 'direction' => 'asc']],
                ],
                'chart_config' => null,
            ],
            [
                'name' => 'Sales by Product',
                'slug' => 'sales-by-product',
                'description' => 'Penjualan per produk — total quantity dan revenue.',
                'category' => 'sales',
                'query_type' => 'chart',
                'query_config' => [
                    'table_name' => 'pos_transaction_items',
                    'joins' => [
                        [
                            'table' => 'products',
                            'first' => 'pos_transaction_items.product_id',
                            'operator' => '=',
                            'second' => 'products.id',
                            'type' => 'left',
                        ],
                        [
                            'table' => 'pos_transactions',
                            'first' => 'pos_transaction_items.transaction_id',
                            'operator' => '=',
                            'second' => 'pos_transactions.id',
                            'type' => 'inner',
                        ],
                    ],
                    'select' => [
                        'products.name as product_name',
                        'SUM(pos_transaction_items.quantity) as total_qty',
                        'SUM(pos_transaction_items.subtotal) as total_revenue',
                    ],
                    'group_by' => ['product_name'],
                    'sort' => [['column' => 'total_revenue', 'direction' => 'desc']],
                    'limit' => 20,
                ],
                'chart_config' => [
                    'type' => 'bar',
                    'x_axis' => 'product_name',
                    'y_axis' => 'total_revenue',
                    'colors' => ['#4f46e5', '#7c3aed', '#2563eb', '#059669', '#d97706'],
                ],
            ],
            [
                'name' => 'Cash Flow Statement',
                'slug' => 'cash-flow-statement',
                'description' => 'Laporan arus kas — pemasukan vs pengeluaran per bulan.',
                'category' => 'finance',
                'query_type' => 'chart',
                'query_config' => [
                    'table_name' => 'journal_entries',
                    'joins' => [
                        [
                            'table' => 'journals',
                            'first' => 'journal_entries.journal_id',
                            'operator' => '=',
                            'second' => 'journals.id',
                            'type' => 'inner',
                        ],
                    ],
                    'select' => [
                        "DATE_FORMAT(journals.journal_date, '%Y-%m') as period",
                        'SUM(journal_entries.debit) as cash_out',
                        'SUM(journal_entries.credit) as cash_in',
                        'SUM(journal_entries.credit) - SUM(journal_entries.debit) as net_cash_flow',
                    ],
                    'group_by' => ['period'],
                    'sort' => [['column' => 'period', 'direction' => 'asc']],
                ],
                'chart_config' => [
                    'type' => 'line',
                    'x_axis' => 'period',
                    'y_axis' => ['cash_in', 'cash_out', 'net_cash_flow'],
                    'colors' => ['#059669', '#dc2626', '#4f46e5'],
                ],
            ],
            [
                'name' => 'Budget vs Actual',
                'slug' => 'budget-vs-actual',
                'description' => 'Perbandingan budget vs realisasi per kategori.',
                'category' => 'finance',
                'query_type' => 'chart',
                'query_config' => [
                    'table_name' => 'budgets',
                    'joins' => [
                        [
                            'table' => 'budget_items',
                            'first' => 'budgets.id',
                            'operator' => '=',
                            'second' => 'budget_items.budget_id',
                            'type' => 'left',
                        ],
                    ],
                    'select' => [
                        'budgets.name as budget_name',
                        'SUM(budget_items.amount) as planned_amount',
                        'SUM(budget_items.actual_amount) as actual_amount',
                        'SUM(budget_items.amount) - SUM(budget_items.actual_amount) as variance',
                    ],
                    'group_by' => ['budget_name'],
                    'sort' => [['column' => 'budget_name', 'direction' => 'asc']],
                ],
                'chart_config' => [
                    'type' => 'bar',
                    'x_axis' => 'budget_name',
                    'y_axis' => ['planned_amount', 'actual_amount'],
                    'colors' => ['#2563eb', '#059669'],
                ],
            ],
        ];

        $count = 0;
        foreach ($templates as $tpl) {
            $existing = ReportTemplate::where('slug', $tpl['slug'])->first();

            if ($existing) {
                $existing->update([
                    'name' => $tpl['name'],
                    'description' => $tpl['description'],
                    'category' => $tpl['category'],
                    'query_type' => $tpl['query_type'],
                    'query_config' => $tpl['query_config'],
                    'chart_config' => $tpl['chart_config'],
                    'is_system' => true,
                    'is_public' => true,
                ]);
            } else {
                ReportTemplate::create([
                    'company_id' => $companyId,
                    'name' => $tpl['name'],
                    'slug' => $tpl['slug'],
                    'description' => $tpl['description'],
                    'category' => $tpl['category'],
                    'query_type' => $tpl['query_type'],
                    'query_config' => $tpl['query_config'],
                    'chart_config' => $tpl['chart_config'],
                    'is_system' => true,
                    'is_public' => true,
                    'created_by' => $adminUserId,
                ]);
            }

            $count++;
        }

        $this->command->info("  System report templates seeded: {$count}");
    }
}

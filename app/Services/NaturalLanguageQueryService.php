<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Employee;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\NlQueryLog;
use App\Models\PosTransaction;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Natural Language Query — pertanyaan Bahasa Indonesia diubah menjadi query database.
 *
 * Keamanan: query DIBANGUN dari whitelist intent (pattern yang sudah ditentukan),
 * TIDAK PERNAH memakai raw SQL dari input user maupun eval. Semua query
 * dipaksa memakai company_id dari user yang sedang login.
 */
class NaturalLanguageQueryService
{
    protected ?AiProvider $provider = null;

    /**
     * Daftar whitelist intent yang didukung.
     * 'classification' memakai 7 kategori utama sesuai spesifikasi.
     */
    public const INTENTS = [
        'sales_total' => [
            'label' => 'Total Penjualan',
            'classification' => 'revenue_query',
            'example' => 'Berapa penjualan bulan ini?',
            'description' => 'Menjumlahkan invoice & transaksi POS yang sudah dibayar dalam rentang waktu tertentu.',
            'chart_type' => 'bar',
        ],
        'sales_by_branch' => [
            'label' => 'Penjualan per Cabang',
            'classification' => 'revenue_query',
            'example' => 'Penjualan cabang Jakarta tahun ini?',
            'description' => 'Menjumlahkan penjualan pada cabang tertentu dalam rentang waktu tertentu.',
            'chart_type' => 'bar',
        ],
        'expense_total' => [
            'label' => 'Total Pengeluaran',
            'classification' => 'expense_query',
            'example' => 'Berapa pengeluaran bulan ini?',
            'description' => 'Menjumlahkan invoice pembelian (purchase) yang sudah dibayar.',
            'chart_type' => 'bar',
        ],
        'employee_count' => [
            'label' => 'Jumlah Karyawan',
            'classification' => 'hr_query',
            'example' => 'Berapa jumlah karyawan aktif?',
            'description' => 'Menghitung jumlah karyawan aktif beserta komposisi tipe karyawan.',
            'chart_type' => 'doughnut',
        ],
        'low_stock' => [
            'label' => 'Stok Menipis',
            'classification' => 'inventory_query',
            'example' => 'Stok produk apa yang menipis?',
            'description' => 'Menampilkan produk dengan stok di bawah stok minimum.',
            'chart_type' => 'bar',
        ],
        'unpaid_invoices' => [
            'label' => 'Piutang Belum Dibayar',
            'classification' => 'general',
            'example' => 'Berapa piutang yang belum dibayar?',
            'description' => 'Menjumlahkan sisa tagihan invoice yang belum lunas (sent/partial/overdue).',
            'chart_type' => 'doughnut',
        ],
        'top_customers' => [
            'label' => 'Pelanggan Teratas',
            'classification' => 'customer_query',
            'example' => 'Top 5 customer bulan ini?',
            'description' => 'Menampilkan pelanggan dengan pendapatan terbesar.',
            'chart_type' => 'bar',
        ],
        'supplier_delivery' => [
            'label' => 'Keterlambatan Supplier',
            'classification' => 'general',
            'example' => 'Supplier mana yang paling sering telat?',
            'description' => 'Mengurutkan supplier berdasarkan jumlah penerimaan barang yang terlambat.',
            'chart_type' => 'bar',
        ],
        'bank_balance' => [
            'label' => 'Kas di Bank',
            'classification' => 'general',
            'example' => 'Berapa kas di bank saat ini?',
            'description' => 'Menjumlahkan saldo rekening bank aktif beserta rinciannya.',
            'chart_type' => 'doughnut',
        ],
    ];

    // ─── MAIN ENTRY ───

    public function query(string $question, User $user): array
    {
        $start = microtime(true);
        $question = trim($question);

        $classification = $this->classifyQuestion($question, $user);
        $intent = $classification['intent'] ?? 'general';

        if ($intent === 'general' || !array_key_exists($intent, self::INTENTS)) {
            $result = $this->executeGeneralSearch($question, $user);
        } else {
            $result = $this->executeIntent($intent, $question, $user, $classification);
        }

        $answerText = $this->buildAnswer($result);

        $output = [
            'answer_text' => $answerText,
            'intent' => $result['intent'],
            'classification' => $result['classification'],
            'chart_type' => $result['chart_type'] ?? null,
            'chart_labels' => $result['chart_labels'] ?? [],
            'chart_data' => $result['chart_data'] ?? [],
            'columns' => $result['columns'] ?? [],
            'data' => $result['rows'] ?? [],
            'execution_time_ms' => (int) round((microtime(true) - $start) * 1000),
        ];

        $this->logQuery($question, $output, $user);

        return $output;
    }

    // ─── SCHEMA ───

    public function getQuerySchema(): array
    {
        return collect(self::INTENTS)
            ->map(fn($meta, $intent) => array_merge(['intent' => $intent], $meta))
            ->values()
            ->toArray();
    }

    // ─── CLASSIFICATION ───

    protected function classifyQuestion(string $question, User $user): array
    {
        $result = null;

        $ai = $this->classifyWithAi($question);
        if ($ai !== null) {
            $intent = $ai['intent'] ?? null;
            if (is_string($intent) && array_key_exists($intent, self::INTENTS)) {
                $result = $ai;
            }
        }

        if ($result === null) {
            $result = $this->classifyWithKeywords($question, $user);
        }

        // Structured entity (cabang, rentang waktu, limit) selalu diekstrak via aturan
        // agar akurat, lalu digabung dengan hasil AI.
        $result['params'] = array_merge($this->extractKeywordParams($question, $user), $result['params'] ?? []);

        // Normalisasi: pertanyaan penjualan yang menyebut cabang => sales_by_branch.
        if (in_array($result['intent'], ['sales_total', 'sales_by_branch'], true)
            && !empty($result['params']['branch_id'])) {
            $result['intent'] = 'sales_by_branch';
        }

        return $result;
    }

    protected function extractKeywordParams(string $question, User $user): array
    {
        return [
            'branch_id' => $this->resolveBranch($question, $user),
            'time_range' => $this->resolveTimeRange($question)['label'],
            'limit' => $this->resolveLimit($question),
        ];
    }

    protected function classifyWithKeywords(string $question, User $user): array
    {
        $q = mb_strtolower($question);

        $params = $this->extractKeywordParams($question, $user);

        if ($this->containsAny($q, ['penjualan', 'pendapatan', 'omzet', 'revenue', 'sales', 'jual'])) {
            if ($params['branch_id'] || $this->containsAny($q, ['cabang', 'branch'])) {
                return ['classification' => 'revenue_query', 'intent' => 'sales_by_branch', 'params' => $params];
            }
            return ['classification' => 'revenue_query', 'intent' => 'sales_total', 'params' => $params];
        }

        if ($this->containsAny($q, ['pengeluaran', 'belanja', 'biaya', 'expense', 'pembelian', 'purchase'])) {
            return ['classification' => 'expense_query', 'intent' => 'expense_total', 'params' => $params];
        }

        if ($this->containsAny($q, ['karyawan', 'pegawai', 'employee', 'staff', 'pekerja', 'jumlah sd'])) {
            return ['classification' => 'hr_query', 'intent' => 'employee_count', 'params' => $params];
        }

        if ($this->containsAny($q, ['stok', 'stock', 'menipis', 'habis', 'minimum', 'persediaan', 'produk'])) {
            return ['classification' => 'inventory_query', 'intent' => 'low_stock', 'params' => $params];
        }

        if ($this->containsAny($q, ['piutang', 'belum dibayar', 'tagihan', 'unpaid', 'receivable', 'hutang pelanggan'])) {
            return ['classification' => 'general', 'intent' => 'unpaid_invoices', 'params' => $params];
        }

        if ($this->containsAny($q, ['kas', 'bank', 'saldo', 'rekening', 'balance', 'tabungan'])) {
            return ['classification' => 'general', 'intent' => 'bank_balance', 'params' => $params];
        }

        if ($this->containsAny($q, ['supplier', 'vendor', 'telat', 'terlambat', 'delivery', 'kirim'])) {
            return ['classification' => 'general', 'intent' => 'supplier_delivery', 'params' => $params];
        }

        if ($this->containsAny($q, ['customer', 'pelanggan', 'klien', 'client', 'top', 'terbaik', 'terlaris', 'terbesar'])) {
            return ['classification' => 'customer_query', 'intent' => 'top_customers', 'params' => $params];
        }

        return ['classification' => 'general', 'intent' => 'general', 'params' => $params];
    }

    protected function classifyWithAi(string $question): ?array
    {
        $provider = $this->tryGetProvider();
        if (!$provider) {
            return null;
        }

        $schema = collect(self::INTENTS)
            ->map(fn($m, $i) => "- {$i}: {$m['description']} (contoh: \"{$m['example']}\")")
            ->implode("\n");

        $system = <<<PROMPT
Anda adalah mesin klasifikasi pertanyaan bahasa Indonesia untuk sistem query database BizOS.
Klasifikasikan pertanyaan pengguna menjadi salah satu intent berikut:

{$schema}

Balas HANYA dengan JSON valid (tanpa markdown, tanpa penjelasan) dalam format:
{"classification":"<kategori>","intent":"<intent>","time_range":"<this_month|last_month|this_year|today|this_week|all>","aggregation":"<sum|count|avg>","limit":<angka>}

Kategori yang valid: revenue_query, expense_query, sales_query, inventory_query, hr_query, customer_query, general.
Jika tidak ada intent yang cocok, kembalikan intent "general".
PROMPT;

        $content = $this->callLlm($provider, $system, $question);

        if ($content === null) {
            return null;
        }

        $json = $this->extractJson($content);
        if ($json === null) {
            return null;
        }

        return [
            'classification' => $json['classification'] ?? 'general',
            'intent' => $json['intent'] ?? 'general',
            'params' => [
                'time_range' => $json['time_range'] ?? null,
                'aggregation' => $json['aggregation'] ?? null,
                'limit' => (int) ($json['limit'] ?? 5),
            ],
        ];
    }

    // ─── INTENT EXECUTION (WHITELIST) ───

    protected function executeIntent(string $intent, string $question, User $user, array $classification): array
    {
        return match ($intent) {
            'sales_total' => $this->runSalesTotal($question, $user),
            'sales_by_branch' => $this->runSalesByBranch($question, $user, $classification),
            'expense_total' => $this->runExpenseTotal($question, $user),
            'employee_count' => $this->runEmployeeCount($user),
            'low_stock' => $this->runLowStock($user),
            'unpaid_invoices' => $this->runUnpaidInvoices($user),
            'top_customers' => $this->runTopCustomers($question, $user),
            'supplier_delivery' => $this->runSupplierDelivery($user),
            'bank_balance' => $this->runBankBalance($user),
            default => $this->executeGeneralSearch($question, $user),
        };
    }

    protected function runSalesTotal(string $question, User $user): array
    {
        $range = $this->resolveTimeRange($question);

        $invoices = Invoice::where('company_id', $user->company_id)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$range['start'], $range['end']])
            ->get(['invoice_date', 'total']);

        $pos = PosTransaction::where('company_id', $user->company_id)
            ->where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$range['start'], $range['end']])
            ->get(['transaction_date', 'grand_total']);

        $invoiceTotal = $invoices->sum('total');
        $posTotal = $pos->sum('grand_total');
        $total = (float) $invoiceTotal + (float) $posTotal;

        $byMonth = $this->aggregateSalesByMonth($invoices, $pos);

        return [
            'intent' => 'sales_total',
            'classification' => 'revenue_query',
            'meta' => [
                'total' => $total,
                'invoice_total' => (float) $invoiceTotal,
                'pos_total' => (float) $posTotal,
                'invoice_count' => $invoices->count(),
                'pos_count' => $pos->count(),
                'period_label' => $range['label'],
            ],
            'rows' => [
                ['label' => 'Invoice', 'value' => $this->rupiah($invoiceTotal)],
                ['label' => 'POS / Retail', 'value' => $this->rupiah($posTotal)],
                ['label' => 'Total', 'value' => $this->rupiah($total)],
            ],
            'columns' => [['key' => 'label', 'label' => 'Sumber'], ['key' => 'value', 'label' => 'Nilai']],
            'chart_type' => 'bar',
            'chart_labels' => $byMonth['labels'],
            'chart_data' => $byMonth['data'],
        ];
    }

    protected function runSalesByBranch(string $question, User $user, array $classification): array
    {
        $branchId = $classification['params']['branch_id'] ?? $this->resolveBranch($question, $user);

        if (!$branchId) {
            $branches = Branch::where('company_id', $user->company_id)->orderBy('name')->get(['id', 'name']);
            return [
                'intent' => 'sales_by_branch',
                'classification' => 'revenue_query',
                'meta' => ['branch_found' => false],
                'rows' => $branches->map(fn($b) => ['id' => $b->id, 'name' => $b->name, 'value' => '-'])->toArray(),
                'columns' => [['key' => 'name', 'label' => 'Cabang'], ['key' => 'value', 'label' => 'Nilai']],
                'chart_type' => null,
                'chart_labels' => [],
                'chart_data' => [],
            ];
        }

        $branch = Branch::find($branchId);
        $range = $this->resolveTimeRange($question);

        $invoiceTotal = Invoice::where('company_id', $user->company_id)
            ->where('branch_id', $branchId)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$range['start'], $range['end']])
            ->sum('total');

        $posTotal = PosTransaction::where('company_id', $user->company_id)
            ->where('branch_id', $branchId)
            ->where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$range['start'], $range['end']])
            ->sum('grand_total');

        $total = (float) $invoiceTotal + (float) $posTotal;

        return [
            'intent' => 'sales_by_branch',
            'classification' => 'revenue_query',
            'meta' => [
                'branch_name' => $branch?->name ?? 'Cabang',
                'total' => $total,
                'invoice_total' => (float) $invoiceTotal,
                'pos_total' => (float) $posTotal,
                'period_label' => $range['label'],
            ],
            'rows' => [
                ['label' => 'Invoice', 'value' => $this->rupiah($invoiceTotal)],
                ['label' => 'POS / Retail', 'value' => $this->rupiah($posTotal)],
                ['label' => 'Total', 'value' => $this->rupiah($total)],
            ],
            'columns' => [['key' => 'label', 'label' => 'Sumber'], ['key' => 'value', 'label' => 'Nilai']],
            'chart_type' => 'bar',
            'chart_labels' => ['Invoice', 'POS / Retail'],
            'chart_data' => [(float) $invoiceTotal, (float) $posTotal],
        ];
    }

    protected function runExpenseTotal(string $question, User $user): array
    {
        $range = $this->resolveTimeRange($question);

        $purchaseInvoices = Invoice::where('company_id', $user->company_id)
            ->where('invoice_type', 'purchase')
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$range['start'], $range['end']])
            ->get(['invoice_date', 'total']);

        $total = (float) $purchaseInvoices->sum('total');

        $byMonth = [];
        foreach ($purchaseInvoices as $inv) {
            $key = $inv->invoice_date->format('Y-m');
            $byMonth[$key] = ($byMonth[$key] ?? 0) + (float) $inv->total;
        }
        ksort($byMonth);

        return [
            'intent' => 'expense_total',
            'classification' => 'expense_query',
            'meta' => [
                'total' => $total,
                'count' => $purchaseInvoices->count(),
                'period_label' => $range['label'],
            ],
            'rows' => [['label' => 'Total Pengeluaran', 'value' => $this->rupiah($total)]],
            'columns' => [['key' => 'label', 'label' => 'Keterangan'], ['key' => 'value', 'label' => 'Nilai']],
            'chart_type' => 'bar',
            'chart_labels' => $this->formatMonthLabels(array_keys($byMonth)),
            'chart_data' => array_values($byMonth),
        ];
    }

    protected function runEmployeeCount(User $user): array
    {
        $employees = Employee::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->get(['employee_type']);

        $count = $employees->count();
        $byType = [];
        foreach ($employees as $e) {
            $type = $e->employee_type ?: 'lainnya';
            $byType[$type] = ($byType[$type] ?? 0) + 1;
        }
        arsort($byType);

        $labels = ['Permanen' => 'permanent', 'Kontrak' => 'contract', 'Percobaan' => 'probation', 'Magang' => 'intern', 'Freelance' => 'freelance', 'Paruh Waktu' => 'part_time'];

        $rows = [];
        foreach ($byType as $type => $c) {
            $rows[] = ['label' => $labels[$type] ?? ucfirst($type), 'value' => $c];
        }

        return [
            'intent' => 'employee_count',
            'classification' => 'hr_query',
            'meta' => ['count' => $count, 'by_type' => $byType],
            'rows' => $rows,
            'columns' => [['key' => 'label', 'label' => 'Tipe Karyawan'], ['key' => 'value', 'label' => 'Jumlah']],
            'chart_type' => 'doughnut',
            'chart_labels' => array_column($rows, 'label'),
            'chart_data' => array_column($rows, 'value'),
        ];
    }

    protected function runLowStock(User $user): array
    {
        $products = Product::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->orderBy('stock')
            ->get(['name', 'code', 'stock', 'min_stock', 'unit']);

        $rows = $products->map(fn($p) => [
            'name' => $p->name,
            'code' => $p->code,
            'stock' => (float) $p->stock,
            'min_stock' => (float) $p->min_stock,
            'unit' => $p->unit ?: '-',
        ])->toArray();

        $chartProducts = array_slice($products->toArray(), 0, 10);

        return [
            'intent' => 'low_stock',
            'classification' => 'inventory_query',
            'meta' => ['count' => count($rows)],
            'rows' => $rows,
            'columns' => [
                ['key' => 'name', 'label' => 'Produk'],
                ['key' => 'code', 'label' => 'Kode'],
                ['key' => 'stock', 'label' => 'Stok'],
                ['key' => 'min_stock', 'label' => 'Min. Stok'],
                ['key' => 'unit', 'label' => 'Satuan'],
            ],
            'chart_type' => 'bar',
            'chart_labels' => array_map(fn($p) => $p['name'], $chartProducts),
            'chart_data' => array_map(fn($p) => (float) $p['stock'], $chartProducts),
        ];
    }

    protected function runUnpaidInvoices(User $user): array
    {
        $invoices = Invoice::where('company_id', $user->company_id)
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->where('remaining_amount', '>', 0)
            ->orderByDesc('remaining_amount')
            ->get(['invoice_number', 'status', 'due_date', 'remaining_amount']);

        $total = (float) $invoices->sum('remaining_amount');

        $byStatus = [];
        foreach ($invoices as $inv) {
            $byStatus[$inv->status] = ($byStatus[$inv->status] ?? 0) + (float) $inv->remaining_amount;
        }

        $rows = $invoices->take(20)->map(fn($inv) => [
            'invoice_number' => $inv->invoice_number,
            'status' => $this->invoiceStatusLabel($inv->status),
            'due_date' => $inv->due_date?->format('d/m/Y') ?? '-',
            'remaining' => (float) $inv->remaining_amount,
        ])->toArray();

        $statusLabels = ['sent' => 'Terkirim', 'partial' => 'Sebagian', 'overdue' => 'Jatuh Tempo'];
        $chartLabels = [];
        $chartData = [];
        foreach ($byStatus as $status => $amount) {
            $chartLabels[] = $statusLabels[$status] ?? ucfirst($status);
            $chartData[] = $amount;
        }

        return [
            'intent' => 'unpaid_invoices',
            'classification' => 'general',
            'meta' => ['total' => $total, 'count' => $invoices->count()],
            'rows' => $rows,
            'columns' => [
                ['key' => 'invoice_number', 'label' => 'No. Invoice'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'due_date', 'label' => 'Jatuh Tempo'],
                ['key' => 'remaining', 'label' => 'Sisa Tagihan'],
            ],
            'chart_type' => 'doughnut',
            'chart_labels' => $chartLabels,
            'chart_data' => $chartData,
        ];
    }

    protected function runTopCustomers(string $question, User $user): array
    {
        $range = $this->resolveTimeRange($question);
        $limit = $this->resolveLimit($question);

        $salesInvoices = SalesInvoice::where('company_id', $user->company_id)
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->get(['client_id', 'total']);

        $byClient = [];
        foreach ($salesInvoices as $si) {
            if (!$si->client_id) {
                continue;
            }
            $byClient[$si->client_id] = ($byClient[$si->client_id] ?? 0) + (float) $si->total;
        }

        arsort($byClient);
        $top = array_slice($byClient, 0, $limit, true);

        $clientNames = Client::whereIn('id', array_keys($top))->pluck('name', 'id');

        $rows = [];
        foreach ($top as $clientId => $total) {
            $rows[] = [
                'name' => $clientNames[$clientId] ?? "Klien #{$clientId}",
                'total' => $total,
            ];
        }

        return [
            'intent' => 'top_customers',
            'classification' => 'customer_query',
            'meta' => ['count' => count($rows), 'period_label' => $range['label']],
            'rows' => $rows,
            'columns' => [['key' => 'name', 'label' => 'Pelanggan'], ['key' => 'total', 'label' => 'Nilai']],
            'chart_type' => 'bar',
            'chart_labels' => array_column($rows, 'name'),
            'chart_data' => array_column($rows, 'total'),
        ];
    }

    protected function runSupplierDelivery(User $user): array
    {
        $receipts = GoodsReceipt::where('company_id', $user->company_id)
            ->whereHas('purchaseOrder', fn($q) => $q->where('company_id', $user->company_id))
            ->with('purchaseOrder:id,company_id,supplier_id,expected_date,po_number')
            ->get(['id', 'purchase_order_id', 'receipt_date']);

        $lateCount = [];
        $totalCount = [];
        foreach ($receipts as $gr) {
            $po = $gr->purchaseOrder;
            if (!$po || !$po->supplier_id) {
                continue;
            }
            $supplierId = $po->supplier_id;
            $totalCount[$supplierId] = ($totalCount[$supplierId] ?? 0) + 1;
            if ($po->expected_date && $gr->receipt_date && $gr->receipt_date->gt($po->expected_date)) {
                $lateCount[$supplierId] = ($lateCount[$supplierId] ?? 0) + 1;
            }
        }

        arsort($lateCount);
        $top = array_slice($lateCount, 0, 10, true);

        $supplierNames = Supplier::whereIn('id', array_keys($totalCount))->pluck('name', 'id');

        $rows = [];
        foreach ($top as $supplierId => $late) {
            $rows[] = [
                'name' => $supplierNames[$supplierId] ?? "Supplier #{$supplierId}",
                'late_count' => $late,
                'total_count' => $totalCount[$supplierId] ?? $late,
            ];
        }

        return [
            'intent' => 'supplier_delivery',
            'classification' => 'general',
            'meta' => ['count' => count($rows)],
            'rows' => $rows,
            'columns' => [
                ['key' => 'name', 'label' => 'Supplier'],
                ['key' => 'late_count', 'label' => 'Terlambat'],
                ['key' => 'total_count', 'label' => 'Total Penerimaan'],
            ],
            'chart_type' => 'bar',
            'chart_labels' => array_column($rows, 'name'),
            'chart_data' => array_column($rows, 'late_count'),
        ];
    }

    protected function runBankBalance(User $user): array
    {
        $accounts = BankAccount::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->orderByDesc('current_balance')
            ->get(['bank_name', 'account_number', 'current_balance']);

        $total = (float) $accounts->sum('current_balance');

        $rows = $accounts->map(fn($a) => [
            'bank_name' => $a->bank_name,
            'account_number' => $a->account_number,
            'balance' => (float) $a->current_balance,
        ])->toArray();

        return [
            'intent' => 'bank_balance',
            'classification' => 'general',
            'meta' => ['total' => $total, 'count' => $accounts->count()],
            'rows' => $rows,
            'columns' => [
                ['key' => 'bank_name', 'label' => 'Bank'],
                ['key' => 'account_number', 'label' => 'No. Rekening'],
                ['key' => 'balance', 'label' => 'Saldo'],
            ],
            'chart_type' => 'doughnut',
            'chart_labels' => array_column($rows, 'bank_name'),
            'chart_data' => array_column($rows, 'balance'),
        ];
    }

    protected function executeGeneralSearch(string $question, User $user): array
    {
        $service = app(EnterpriseSearchService::class);
        $search = $service->naturalLanguageSearch($question, 6);

        return [
            'intent' => 'general',
            'classification' => 'general',
            'meta' => ['count' => count($search['data'] ?? [])],
            'rows' => $search['data'] ?? [],
            'columns' => $search['columns'] ?? [],
            'chart_type' => null,
            'chart_labels' => [],
            'chart_data' => [],
            'search_fallback' => true,
        ];
    }

    // ─── ANSWER BUILDER ───

    public function buildAnswer(array $result): string
    {
        $intent = $result['intent'] ?? 'general';
        $meta = $result['meta'] ?? [];

        return match ($intent) {
            'sales_total' => sprintf(
                "Total penjualan %s adalah %s, terdiri dari %s invoice (Rp %s) dan %s transaksi POS (Rp %s).",
                $meta['period_label'] ?? 'pada periode yang dipilih',
                '<strong>' . $this->rupiah($meta['total'] ?? 0) . '</strong>',
                number_format($meta['invoice_count'] ?? 0, 0, ',', '.'),
                $this->rupiah($meta['invoice_total'] ?? 0),
                number_format($meta['pos_count'] ?? 0, 0, ',', '.'),
                $this->rupiah($meta['pos_total'] ?? 0),
            ),
            'sales_by_branch' => isset($meta['branch_found']) && !$meta['branch_found']
                ? 'Cabang yang dimaksud tidak ditemukan. Berikut daftar cabang yang tersedia — silakan sebutkan nama cabangnya.'
                : sprintf(
                    "Penjualan cabang %s %s adalah %s (Invoice %s + POS %s).",
                    $meta['branch_name'] ?? 'terpilih',
                    $meta['period_label'] ?? 'pada periode yang dipilih',
                    '<strong>' . $this->rupiah($meta['total'] ?? 0) . '</strong>',
                    $this->rupiah($meta['invoice_total'] ?? 0),
                    $this->rupiah($meta['pos_total'] ?? 0),
                ),
            'expense_total' => sprintf(
                "Total pengeluaran %s adalah %s dari %s invoice pembelian yang sudah dibayar.",
                $meta['period_label'] ?? 'pada periode yang dipilih',
                '<strong>' . $this->rupiah($meta['total'] ?? 0) . '</strong>',
                number_format($meta['count'] ?? 0, 0, ',', '.'),
            ),
            'employee_count' => sprintf(
                "Saat ini terdapat <strong>%s</strong> karyawan aktif.",
                number_format($meta['count'] ?? 0, 0, ',', '.'),
            ),
            'low_stock' => ($meta['count'] ?? 0) > 0
                ? sprintf('Terdapat <strong>%s</strong> produk yang stoknya menipis (di bawah stok minimum).', number_format($meta['count'] ?? 0, 0, ',', '.'))
                : 'Tidak ada produk yang stoknya menipis. Semua stok berada di atas batas minimum.',
            'unpaid_invoices' => sprintf(
                "Total piutang yang belum dibayar adalah %s dari %s invoice.",
                '<strong>' . $this->rupiah($meta['total'] ?? 0) . '</strong>',
                number_format($meta['count'] ?? 0, 0, ',', '.'),
            ),
            'top_customers' => sprintf(
                "Berikut %s pelanggan dengan pendapatan terbesar %s.",
                number_format($meta['count'] ?? 0, 0, ',', '.'),
                $meta['period_label'] ?? 'pada periode yang dipilih',
            ),
            'supplier_delivery' => ($meta['count'] ?? 0) > 0
                ? sprintf('Berikut <strong>%s</strong> supplier dengan keterlambatan pengiriman terbanyak.', number_format($meta['count'] ?? 0, 0, ',', '.'))
                : 'Tidak ditemukan keterlambatan pengiriman dari supplier.',
            'bank_balance' => sprintf(
                "Total kas di bank saat ini adalah %s, tersebar di %s rekening.",
                '<strong>' . $this->rupiah($meta['total'] ?? 0) . '</strong>',
                number_format($meta['count'] ?? 0, 0, ',', '.'),
            ),
            default => $this->buildGeneralAnswer($result),
        };
    }

    protected function buildGeneralAnswer(array $result): string
    {
        $count = count($result['rows'] ?? []);
        if ($count === 0) {
            return 'Maaf, saya tidak menemukan data yang relevan untuk pertanyaan tersebut. Coba gunakan kalimat seperti "Berapa penjualan bulan ini?" atau pilih salah satu saran di bawah.';
        }
        return sprintf('Berikut %s hasil yang paling relevan dengan pertanyaan Anda.', number_format($count, 0, ',', '.'));
    }

    // ─── LOGGING ───

    public function logQuery(string $question, array $result, User $user): void
    {
        try {
            NlQueryLog::create([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'question' => mb_substr($question, 0, 2000),
                'intent' => $result['intent'] ?? null,
                'classification' => $result['classification'] ?? null,
                'answer_text' => Str::limit($result['answer_text'] ?? '', 2000),
                'execution_time_ms' => $result['execution_time_ms'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('NL query logging failed: ' . $e->getMessage());
        }
    }

    public function getHistory(User $user, int $limit = 20): array
    {
        return NlQueryLog::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get(['id', 'question', 'intent', 'classification', 'answer_text', 'created_at'])
            ->map(fn($log) => [
                'id' => $log->id,
                'question' => $log->question,
                'intent' => $log->intent,
                'classification' => $log->classification,
                'answer' => Str::limit($log->answer_text ?? '', 140),
                'at' => $log->created_at?->diffForHumans(),
            ])
            ->toArray();
    }

    // ─── PROVIDER ───

    public function getProvider(): AiProvider
    {
        if ($this->provider) {
            return $this->provider;
        }

        $this->provider = AiProvider::where('is_active', true)
            ->where('api_format', 'openai_compatible')
            ->first();

        if (!$this->provider) {
            throw new \RuntimeException('Tidak ada AI Provider aktif dengan format openai_compatible.');
        }

        return $this->provider;
    }

    protected function tryGetProvider(): ?AiProvider
    {
        if ($this->provider) {
            return $this->provider;
        }

        return AiProvider::where('is_active', true)
            ->where('api_format', 'openai_compatible')
            ->first();
    }

    protected function callLlm(AiProvider $provider, string $systemPrompt, string $userMessage): ?string
    {
        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = decrypt($provider->api_key_encrypted);
        $model = $provider->default_model ?: 'gpt-4o-mini';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(45)
                ->post("{$baseUrl}/v1/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0,
                    'max_tokens' => 500,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('NL Query LLM error', ['status' => $response->status()]);
            return null;
        } catch (ConnectionException $e) {
            Log::error('NL Query connection error: ' . $e->getMessage());
            return null;
        } catch (\Throwable $e) {
            Log::error('NL Query LLM exception: ' . $e->getMessage());
            return null;
        }
    }

    // ─── HELPERS ───

    protected function resolveTimeRange(string $question): array
    {
        $q = mb_strtolower($question);
        $now = now();

        if ($this->containsAny($q, ['bulan ini', 'bulan berjalan', 'this month'])) {
            return ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy(), 'label' => 'bulan ini'];
        }
        if ($this->containsAny($q, ['bulan lalu', 'last month'])) {
            return ['start' => $now->copy()->subMonthNoOverflow()->startOfMonth(), 'end' => $now->copy()->subMonthNoOverflow()->endOfMonth(), 'label' => 'bulan lalu'];
        }
        if ($this->containsAny($q, ['tahun ini', 'tahun berjalan', 'this year'])) {
            return ['start' => $now->copy()->startOfYear(), 'end' => $now->copy(), 'label' => 'tahun ini'];
        }
        if ($this->containsAny($q, ['tahun lalu', 'last year'])) {
            return ['start' => $now->copy()->subYear()->startOfYear(), 'end' => $now->copy()->subYear()->endOfYear(), 'label' => 'tahun lalu'];
        }
        if ($this->containsAny($q, ['minggu ini', 'this week'])) {
            return ['start' => $now->copy()->startOfWeek(), 'end' => $now->copy(), 'label' => 'minggu ini'];
        }
        if ($this->containsAny($q, ['hari ini', 'today', 'hari ini saja'])) {
            return ['start' => $now->copy()->startOfDay(), 'end' => $now->copy(), 'label' => 'hari ini'];
        }
        if ($this->containsAny($q, ['kemarin', 'yesterday'])) {
            return ['start' => $now->copy()->subDay()->startOfDay(), 'end' => $now->copy()->subDay()->endOfDay(), 'label' => 'kemarin'];
        }

        return ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy(), 'label' => 'bulan ini'];
    }

    protected function resolveBranch(string $question, User $user): ?int
    {
        $q = mb_strtolower($question);
        $branches = Branch::where('company_id', $user->company_id)->get(['id', 'name']);

        foreach ($branches as $branch) {
            $name = mb_strtolower(trim($branch->name ?? ''));
            if ($name !== '' && str_contains($q, $name)) {
                return $branch->id;
            }
        }

        return null;
    }

    protected function resolveLimit(string $question): int
    {
        if (preg_match('/top\s*(\d+)/i', $question, $m)) {
            return max(1, min(50, (int) $m[1]));
        }
        return 5;
    }

    protected function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }

    protected function aggregateSalesByMonth($invoices, $pos): array
    {
        $byMonth = [];
        foreach ($invoices as $inv) {
            $key = $inv->invoice_date->format('Y-m');
            $byMonth[$key] = ($byMonth[$key] ?? 0) + (float) $inv->total;
        }
        foreach ($pos as $p) {
            $key = $p->transaction_date->format('Y-m');
            $byMonth[$key] = ($byMonth[$key] ?? 0) + (float) $p->grand_total;
        }
        ksort($byMonth);

        return [
            'labels' => $this->formatMonthLabels(array_keys($byMonth)),
            'data' => array_values($byMonth),
        ];
    }

    protected function formatMonthLabels(array $months): array
    {
        return array_map(function ($month) {
            try {
                return \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('M Y');
            } catch (\Throwable) {
                return $month;
            }
        }, $months);
    }

    protected function invoiceStatusLabel(string $status): string
    {
        return match ($status) {
            'sent' => 'Terkirim',
            'partial' => 'Sebagian',
            'overdue' => 'Jatuh Tempo',
            'paid' => 'Lunas',
            'draft' => 'Draf',
            'cancelled' => 'Batal',
            'void' => 'Void',
            default => ucfirst($status),
        };
    }

    protected function rupiah($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    protected function extractJson(string $content): ?array
    {
        $content = trim($content);

        if (preg_match('/\{.*\}/s', $content, $m)) {
            $content = $m[0];
        }

        $json = json_decode($content, true);

        if (is_array($json)) {
            return $json;
        }

        Log::warning('NL Query: gagal parse JSON dari LLM', ['content' => Str::limit($content, 300)]);
        return null;
    }
}

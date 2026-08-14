<?php

namespace App\Services;

use App\Models\ReportTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PivotTableService
{
    public function getDataSources(): array
    {
        $result = [];

        foreach ($this->sources() as $key => $source) {
            $result[$key] = [
                'label' => $source['label'],
                'table' => $source['table'],
                'fields' => $source['fields'],
            ];
        }

        return $result;
    }

    public function getFields(string $source): array
    {
        $config = $this->sources()[$source] ?? null;

        if (!$config) {
            return ['dimensions' => [], 'measures' => []];
        }

        return [
            'dimensions' => collect($config['fields'])->where('type', 'dimension')->values()->toArray(),
            'measures' => collect($config['fields'])->where('type', 'measure')->values()->toArray(),
        ];
    }

    public function getAggregates(): array
    {
        return [
            'sum' => 'SUM (Jumlah)',
            'avg' => 'AVG (Rata-rata)',
            'count' => 'COUNT (Hitung)',
            'min' => 'MIN (Minimum)',
            'max' => 'MAX (Maximum)',
        ];
    }

    public function getFilterOperators(): array
    {
        return [
            '=' => 'Sama dengan (=)',
            '!=' => 'Tidak sama (!=)',
            '>' => 'Lebih dari (>)',
            '<' => 'Kurang dari (<)',
            '>=' => 'Lebih/sama (>=)',
            '<=' => 'Kurang/sama (<=)',
            'between' => 'Rentang (antara)',
            'contains' => 'Mengandung',
        ];
    }

    public function executePivot(string $source, array $dimensions, array $measures, array $filters): array
    {
        $config = $this->sources()[$source] ?? null;

        if (!$config) {
            return ['error' => 'Sumber data tidak valid.'];
        }

        if (empty($dimensions) && empty($measures)) {
            return ['error' => 'Pilih minimal satu dimensi atau ukuran.'];
        }

        $table = $config['table'];
        $fieldMap = collect($config['fields'])->keyBy('name');

        $query = DB::table($table);

        foreach ($filters as $filter) {
            $this->applyFilterToQuery($query, $filter);
        }

        $select = [];
        $headers = [];

        foreach ($dimensions as $dimension) {
            $field = $fieldMap->get($dimension);
            if (!$field) {
                continue;
            }
            $select[] = $dimension;
            $headers[] = [
                'key' => $dimension,
                'label' => $field['label'],
                'type' => 'dimension',
                'data_type' => $field['data_type'] ?? 'string',
            ];
        }

        foreach ($measures as $measure) {
            $fieldName = $measure['field'] ?? null;
            $aggregate = $measure['aggregate'] ?? 'sum';

            if (!$fieldName) {
                continue;
            }

            $field = $fieldMap->get($fieldName);
            $alias = $this->measureAlias($fieldName, $aggregate);

            if ($aggregate === 'count' || $fieldName === '*') {
                $select[] = DB::raw("COUNT(*) as {$alias}");
                $aggregate = 'count';
            } else {
                $select[] = DB::raw("{$aggregate}({$fieldName}) as {$alias}");
            }

            $headers[] = [
                'key' => $alias,
                'label' => $this->measureLabel($field, $aggregate),
                'type' => 'measure',
                'aggregate' => $aggregate,
                'field' => $fieldName,
            ];
        }

        $query->select($select);

        if (!empty($dimensions)) {
            $query->groupBy($dimensions);
        }

        if (!empty($dimensions)) {
            $query->orderBy($dimensions[0]);
        }

        $rawRows = $query->get();

        $rows = [];
        $totals = [];

        foreach ($headers as $header) {
            if ($header['type'] === 'measure') {
                $totals[$header['key']] = 0;
            }
        }

        foreach ($rawRows as $raw) {
            $row = (array) $raw;
            $normalized = [];

            foreach ($headers as $header) {
                $value = $row[$header['key']] ?? null;

                if ($header['type'] === 'measure') {
                    $value = is_null($value) ? 0 : (float) $value;
                    $totals[$header['key']] += $value;
                }

                $normalized[$header['key']] = $value;
            }

            $rows[] = $normalized;
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
            'totals' => $totals,
            'source' => $source,
            'source_label' => $config['label'],
            'row_count' => count($rows),
        ];
    }

    public function saveReport(string $name, string $source, array $config): ReportTemplate
    {
        $companyId = auth()->user()->company_id ?? null;
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 1;

        while (ReportTemplate::where('company_id', $companyId)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        return ReportTemplate::create([
            'company_id' => $companyId,
            'name' => $name,
            'slug' => $slug,
            'description' => $config['description'] ?? null,
            'category' => 'pivot',
            'query_type' => 'table',
            'report_type' => 'pivot',
            'query_config' => null,
            'pivot_config' => [
                'source' => $source,
                'dimensions' => $config['dimensions'] ?? [],
                'measures' => $config['measures'] ?? [],
                'filters' => $config['filters'] ?? [],
            ],
            'chart_config' => null,
            'is_system' => false,
            'is_public' => false,
            'created_by' => auth()->id(),
        ]);
    }

    public function getSavedReports(): Collection
    {
        $companyId = auth()->user()->company_id ?? null;

        return ReportTemplate::where('company_id', $companyId)
            ->where('report_type', 'pivot')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function exportCsv(array $result): string
    {
        $headers = $result['headers'] ?? [];
        $rows = $result['rows'] ?? [];
        $totals = $result['totals'] ?? [];

        $output = fopen('php://temp', 'r+');

        fputcsv($output, array_map(fn($h) => $h['label'], $headers));

        foreach ($rows as $row) {
            fputcsv($output, array_map(fn($h) => $row[$h['key']] ?? '', $headers));
        }

        if (!empty($headers)) {
            $totalLine = [];
            foreach ($headers as $header) {
                $totalLine[] = $header['type'] === 'dimension'
                    ? 'Total'
                    : ($totals[$header['key']] ?? 0);
            }
            fputcsv($output, $totalLine);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    protected function applyFilterToQuery($query, array $filter): void
    {
        $column = $filter['column'] ?? null;
        $operator = $filter['operator'] ?? '=';
        $value = $filter['value'] ?? null;
        $valueEnd = $filter['value_end'] ?? null;

        if (!$column || $value === null || $value === '') {
            return;
        }

        match ($operator) {
            'between' => $query->whereBetween($column, [$value, $valueEnd !== null && $valueEnd !== '' ? $valueEnd : $value]),
            'contains' => $query->where($column, 'like', "%{$value}%"),
            'in' => $query->whereIn($column, array_map('trim', explode(',', (string) $value))),
            default => $query->where($column, $operator, $value),
        };
    }

    protected function measureAlias(string $field, string $aggregate): string
    {
        $fieldPart = ($field === '*' || $field === '') ? 'all' : $field;

        return "{$aggregate}_{$fieldPart}";
    }

    protected function measureLabel(?array $field, string $aggregate): string
    {
        $fieldLabel = $field['label'] ?? ucwords(str_replace('_', ' ', (string) ($field['name'] ?? '')));

        return "{$fieldLabel} (" . strtoupper($aggregate) . ')';
    }

    protected function sources(): array
    {
        return [
            'invoices' => [
                'label' => 'Invoice',
                'table' => 'invoices',
                'fields' => [
                    ['name' => 'branch_id', 'label' => 'Cabang', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'invoice_type', 'label' => 'Tipe Invoice', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'invoice_date', 'label' => 'Tanggal Invoice', 'type' => 'dimension', 'data_type' => 'date'],
                    ['name' => 'reference_entity', 'label' => 'Entitas Referensi', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'discount_amount', 'label' => 'Diskon', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'tax_amount', 'label' => 'Pajak', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'total', 'label' => 'Total', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'paid_amount', 'label' => 'Dibayar', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'remaining_amount', 'label' => 'Sisa Tagihan', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => '*', 'label' => 'Jumlah Invoice', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'count'],
                ],
            ],
            'sales_orders' => [
                'label' => 'Sales Order',
                'table' => 'sales_orders',
                'fields' => [
                    ['name' => 'client_id', 'label' => 'Klien', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'branch_id', 'label' => 'Cabang', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'order_date', 'label' => 'Tanggal Order', 'type' => 'dimension', 'data_type' => 'date'],
                    ['name' => 'created_by', 'label' => 'Dibuat Oleh', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'tax', 'label' => 'Pajak', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'discount', 'label' => 'Diskon', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'shipping_cost', 'label' => 'Ongkir', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'total', 'label' => 'Total', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => '*', 'label' => 'Jumlah SO', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'count'],
                ],
            ],
            'payments' => [
                'label' => 'Pembayaran',
                'table' => 'payments',
                'fields' => [
                    ['name' => 'branch_id', 'label' => 'Cabang', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'payment_method_id', 'label' => 'Metode Pembayaran', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'payment_date', 'label' => 'Tanggal Bayar', 'type' => 'dimension', 'data_type' => 'date'],
                    ['name' => 'confirmed_by', 'label' => 'Dikonfirmasi Oleh', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'amount', 'label' => 'Jumlah', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => '*', 'label' => 'Jumlah Pembayaran', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'count'],
                ],
            ],
            'pos_transactions' => [
                'label' => 'Transaksi POS',
                'table' => 'pos_transactions',
                'fields' => [
                    ['name' => 'branch_id', 'label' => 'Cabang', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'cashier_id', 'label' => 'Kasir', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'member_id', 'label' => 'Member', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'payment_status', 'label' => 'Status Pembayaran', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'transaction_date', 'label' => 'Tanggal Transaksi', 'type' => 'dimension', 'data_type' => 'date'],
                    ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'discount_total', 'label' => 'Diskon', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'tax_total', 'label' => 'Pajak', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'grand_total', 'label' => 'Grand Total', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => '*', 'label' => 'Jumlah Transaksi', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'count'],
                ],
            ],
            'leads' => [
                'label' => 'Lead',
                'table' => 'leads',
                'fields' => [
                    ['name' => 'source_id', 'label' => 'Sumber Lead', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'assigned_to', 'label' => 'Sales', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'industry', 'label' => 'Industri', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'score', 'label' => 'Skor', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'avg'],
                    ['name' => '*', 'label' => 'Jumlah Lead', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'count'],
                ],
            ],
            'deals' => [
                'label' => 'Deal',
                'table' => 'deals',
                'fields' => [
                    ['name' => 'lead_id', 'label' => 'Lead', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'client_id', 'label' => 'Klien', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'stage_id', 'label' => 'Tahap Pipeline', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'assigned_to', 'label' => 'Sales', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'expected_close_date', 'label' => 'Estimasi Closing', 'type' => 'dimension', 'data_type' => 'date'],
                    ['name' => 'expected_value', 'label' => 'Nilai Estimasi', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => '*', 'label' => 'Jumlah Deal', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'count'],
                ],
            ],
            'employees' => [
                'label' => 'Karyawan',
                'table' => 'employees',
                'fields' => [
                    ['name' => 'branch_id', 'label' => 'Cabang', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'department_id', 'label' => 'Departemen', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'position_id', 'label' => 'Jabatan', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'designation_id', 'label' => 'Pangkat', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'grade_id', 'label' => 'Golongan', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'gender', 'label' => 'Jenis Kelamin', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'marital_status', 'label' => 'Status Pernikahan', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'employee_type', 'label' => 'Tipe Karyawan', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'join_date', 'label' => 'Tanggal Bergabung', 'type' => 'dimension', 'data_type' => 'date'],
                    ['name' => 'basic_salary', 'label' => 'Gaji Pokok', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'hourly_rate', 'label' => 'Tarif Per Jam', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'avg'],
                    ['name' => '*', 'label' => 'Jumlah Karyawan', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'count'],
                ],
            ],
            'purchase_orders' => [
                'label' => 'Purchase Order',
                'table' => 'purchase_orders',
                'fields' => [
                    ['name' => 'supplier_id', 'label' => 'Supplier', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'branch_id', 'label' => 'Cabang', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'warehouse_id', 'label' => 'Gudang', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'order_date', 'label' => 'Tanggal Order', 'type' => 'dimension', 'data_type' => 'date'],
                    ['name' => 'expected_date', 'label' => 'Estimasi Terima', 'type' => 'dimension', 'data_type' => 'date'],
                    ['name' => 'created_by', 'label' => 'Dibuat Oleh', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'tax_amount', 'label' => 'Pajak', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'discount_amount', 'label' => 'Diskon', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'shipping_cost', 'label' => 'Ongkir', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'total', 'label' => 'Total', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => '*', 'label' => 'Jumlah PO', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'count'],
                ],
            ],
            'expenses' => [
                'label' => 'Pengeluaran (Reimbursement)',
                'table' => 'reimbursements',
                'fields' => [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'category_id', 'label' => 'Kategori', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'dimension', 'data_type' => 'string'],
                    ['name' => 'date', 'label' => 'Tanggal', 'type' => 'dimension', 'data_type' => 'date'],
                    ['name' => 'amount', 'label' => 'Jumlah', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => 'paid_amount', 'label' => 'Dibayar', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'sum'],
                    ['name' => '*', 'label' => 'Jumlah Pengeluaran', 'type' => 'measure', 'data_type' => 'number', 'aggregate' => 'count'],
                ],
            ],
        ];
    }
}

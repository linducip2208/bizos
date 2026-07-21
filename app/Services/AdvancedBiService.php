<?php

namespace App\Services;

use App\Models\AdvancedReport;
use App\Models\ReportSchedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdvancedBiService
{
    public function buildPivotTable(array $config): array
    {
        $sourceTable = $config['source_table'] ?? null;
        $rows = $config['rows'] ?? [];
        $columns = $config['columns'] ?? [];
        $values = $config['values'] ?? [];
        $filters = $config['filters'] ?? [];

        if (!$sourceTable) {
            return ['error' => 'source_table diperlukan.'];
        }

        $query = DB::table($sourceTable);

        foreach ($filters as $filter) {
            $this->applyFilterToQuery($query, $filter);
        }

        $selectColumns = array_merge($rows, $columns);
        foreach ($values as $value) {
            $field = $value['field'] ?? null;
            $aggregate = $value['aggregate'] ?? 'sum';
            $alias = $value['alias'] ?? ($aggregate . '_' . $field);

            if ($field) {
                $selectColumns[] = DB::raw("{$aggregate}({$field}) as {$alias}");
            }
        }

        $query->select(...$selectColumns);

        if (!empty($rows)) {
            $query->groupBy(array_merge($rows, $columns));
        }

        $query->orderBy($rows[0] ?? $selectColumns[0]);

        $rawData = $query->get();

        $pivotData = $this->pivotData($rawData, $rows, $columns, $values);

        return [
            'raw' => $rawData,
            'pivot' => $pivotData,
            'config' => $config,
        ];
    }

    public function buildCrossTab(
        string $table,
        string $rowField,
        string $colField,
        string $valueField,
        string $aggregate = 'sum'
    ): array {
        $raw = DB::table($table)
            ->select($rowField, $colField, DB::raw("{$aggregate}({$valueField}) as agg_value"))
            ->groupBy($rowField, $colField)
            ->orderBy($rowField)
            ->get();

        $rowLabels = $raw->pluck($rowField)->unique()->values();
        $colLabels = $raw->pluck($colField)->unique()->values();

        $matrix = [];
        $rowTotals = [];
        $colTotals = array_fill_keys($colLabels->toArray(), 0);
        $grandTotal = 0;

        foreach ($rowLabels as $rowLabel) {
            $matrix[$rowLabel] = [];
            $rowTotal = 0;

            foreach ($colLabels as $colLabel) {
                $cell = $raw->where($rowField, $rowLabel)
                    ->where($colField, $colLabel)
                    ->first();

                $val = $cell ? (float) $cell->agg_value : 0;
                $matrix[$rowLabel][$colLabel] = $val;
                $rowTotal += $val;
                $colTotals[$colLabel] += $val;
            }

            $rowTotals[$rowLabel] = $rowTotal;
            $grandTotal += $rowTotal;
        }

        return [
            'row_field' => $rowField,
            'col_field' => $colField,
            'value_field' => $valueField,
            'aggregate' => $aggregate,
            'row_labels' => $rowLabels,
            'col_labels' => $colLabels,
            'matrix' => $matrix,
            'row_totals' => $rowTotals,
            'col_totals' => $colTotals,
            'grand_total' => $grandTotal,
        ];
    }

    public function drillDown(array $currentConfig, string $drillField, $drillValue): array
    {
        $config = $currentConfig;
        $config['drill_field'] = $drillField;
        $config['drill_value'] = $drillValue;

        if (!isset($config['filters'])) {
            $config['filters'] = [];
        }

        $config['filters'][] = [
            'column' => $drillField,
            'operator' => '=',
            'value' => $drillValue,
        ];

        if (isset($config['source_table'])) {
            return $this->buildPivotTable($config);
        }

        return $config;
    }

    public function addCalculatedField(array $data, string $name, string $formula): array
    {
        $tokens = preg_split('/([\+\-\*\/\(\)])/', $formula, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($data as &$row) {
            $expression = $formula;
            foreach ($row as $key => $value) {
                if (is_numeric($value)) {
                    $expression = str_replace($key, (float) $value, $expression);
                }
            }

            try {
                $row[$name] = $this->evaluateExpression($expression);
            } catch (\Throwable $e) {
                $row[$name] = null;
            }
        }

        return $data;
    }

    public function applyConditionalFormat(array $data, array $rules): array
    {
        $formatted = [];

        foreach ($data as $row) {
            $styles = [];

            foreach ($rules as $rule) {
                $field = $rule['field'] ?? null;
                $operator = $rule['operator'] ?? '>';
                $value = $rule['value'] ?? 0;
                $style = $rule['style'] ?? [];

                if (!$field || !isset($row[$field])) {
                    continue;
                }

                $actual = $row[$field];
                $match = match ($operator) {
                    '>' => (float) $actual > (float) $value,
                    '<' => (float) $actual < (float) $value,
                    '>=' => (float) $actual >= (float) $value,
                    '<=' => (float) $actual <= (float) $value,
                    '=' => $actual == $value,
                    '!=' => $actual != $value,
                    'contains' => str_contains((string) $actual, (string) $value),
                    default => false,
                };

                if ($match) {
                    $styles['bg_color'] = $style['bg_color'] ?? null;
                    $styles['font_color'] = $style['font_color'] ?? null;
                    $styles['icon'] = $style['icon'] ?? null;
                }
            }

            $formatted[] = [
                'data' => $row,
                'styles' => $styles,
            ];
        }

        return $formatted;
    }

    public function saveReport(string $name, array $config, ?int $companyId = null): AdvancedReport
    {
        return AdvancedReport::create([
            'company_id' => $companyId ?? auth()->user()->company_id,
            'name' => $name,
            'type' => $config['type'] ?? 'pivot',
            'config' => $config,
            'data_source' => $config['data_source'] ?? null,
            'row_fields' => $config['rows'] ?? null,
            'column_fields' => $config['columns'] ?? null,
            'value_fields' => $config['values'] ?? null,
            'filters' => $config['filters'] ?? null,
            'calculated_fields' => $config['calculated_fields'] ?? null,
            'conditional_formats' => $config['conditional_formats'] ?? null,
            'chart_config' => $config['chart_config'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }

    public function loadReport(int $reportId): array
    {
        $report = AdvancedReport::findOrFail($reportId);

        $config = $report->config ?? [];
        if (!$config && $report->data_source) {
            $config = [
                'source_table' => $report->data_source['table_name'] ?? null,
                'rows' => $report->row_fields,
                'columns' => $report->column_fields,
                'values' => $report->value_fields,
                'filters' => $report->filters,
            ];
        }

        $result = match ($report->type) {
            'crosstab' => $this->buildCrossTab(
                $config['source_table'] ?? $report->data_source['table_name'] ?? '',
                $config['rows'][0] ?? '',
                $config['columns'][0] ?? '',
                $config['values'][0]['field'] ?? '',
                $config['values'][0]['aggregate'] ?? 'sum'
            ),
            default => $this->buildPivotTable($config),
        };

        return [
            'report' => $report,
            'config' => $config,
            'result' => $result,
        ];
    }

    public function exportToExcelAdvanced(AdvancedReport $report): BinaryFileResponse
    {
        $loaded = $this->loadReport($report->id);
        $data = $loaded['result']['raw'] ?? $loaded['result']['matrix'] ?? [];
        $fileName = Str::slug($report->name) . '_' . now()->format('Ymd_His') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(Str::limit($report->name, 30));

        $row = 1;
        $col = 'A';

        if (!empty($loaded['result']['col_labels']) && !empty($loaded['result']['matrix'])) {
            $sheet->setCellValue('A1', $loaded['result']['row_field'] ?? 'Baris');
            $col = 'B';
            foreach ($loaded['result']['col_labels'] as $colLabel) {
                $sheet->setCellValue($col . '1', $colLabel);
                $col++;
            }
            $sheet->setCellValue($col . '1', 'Total');
            $row = 2;

            foreach ($loaded['result']['row_labels'] as $rowLabel) {
                $sheet->setCellValue('A' . $row, $rowLabel);
                $col = 'B';
                $rowTotal = 0;
                foreach ($loaded['result']['col_labels'] as $colLabel) {
                    $val = $loaded['result']['matrix'][$rowLabel][$colLabel] ?? 0;
                    $sheet->setCellValue($col . $row, $val);
                    $rowTotal += $val;
                    $col++;
                }
                $sheet->setCellValue($col . $row, $rowTotal);
                $row++;
            }
        } elseif (is_array($data) && count($data) > 0 && is_object($data[0] ?? null)) {
            $first = (array) ($data[0] ?? []);
            $headers = array_keys($first);
            foreach ($headers as $idx => $header) {
                $sheet->setCellValue(chr(65 + $idx) . '1', ucwords(str_replace('_', ' ', $header)));
            }
            $row = 2;
            foreach ($data as $record) {
                $record = (array) $record;
                foreach (array_values($record) as $idx => $value) {
                    $sheet->setCellValue(chr(65 + $idx) . $row, $value);
                }
                $row++;
            }
        }

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . '/' . $fileName;
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend();
    }

    public function exportToPdfAdvanced(AdvancedReport $report): BinaryFileResponse
    {
        $loaded = $this->loadReport($report->id);
        $fileName = Str::slug($report->name) . '_' . now()->format('Ymd_His') . '.pdf';

        $html = view('reports.advanced-pdf-template', [
            'report' => $report,
            'data' => $loaded['result'],
            'config' => $loaded['config'],
            'generatedAt' => now()->format('d M Y H:i'),
        ])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'landscape');

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . '/' . $fileName;
        $pdf->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend();
    }

    public function scheduleDelivery(AdvancedReport $report, array $recipients, string $frequency): ReportSchedule
    {
        return ReportSchedule::create([
            'report_template_id' => null,
            'name' => 'Advanced: ' . $report->name,
            'recipients' => $recipients,
            'frequency' => $frequency,
            'time_of_day' => '08:00',
            'format' => 'pdf',
            'is_active' => true,
        ]);
    }

    public function generateEmbedCode(int $reportId): string
    {
        $report = AdvancedReport::findOrFail($reportId);

        if (!$report->embed_token) {
            $report->update(['embed_token' => bin2hex(random_bytes(32))]);
        }

        $url = config('app.url') . '/api/bi/embed/' . $report->embed_token;

        return "<iframe src=\"{$url}\" width=\"100%\" height=\"600\" frameborder=\"0\" style=\"border-radius:8px;border:1px solid #e2e8f0;\"></iframe>";
    }

    public function getEmbedData(string $embedToken): array
    {
        $report = AdvancedReport::where('embed_token', $embedToken)->firstOrFail();

        return $this->loadReport($report->id);
    }

    public function getAvailableTables(): array
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $key = "Tables_in_{$dbName}";

        $result = [];
        foreach ($tables as $table) {
            $tableName = $table->$key;
            $columns = DB::select("SHOW COLUMNS FROM `{$tableName}`");
            $colNames = array_map(fn($c) => $c->Field, $columns);

            $result[] = [
                'table' => $tableName,
                'columns' => $colNames,
                'label' => ucwords(str_replace('_', ' ', $tableName)),
            ];
        }

        return $result;
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

    protected function applyFilterToQuery($query, array $filter): void
    {
        $column = $filter['column'] ?? null;
        $operator = $filter['operator'] ?? '=';
        $value = $filter['value'] ?? null;
        $type = $filter['type'] ?? 'where';

        if (!$column) {
            return;
        }

        match ($type) {
            'whereBetween' => $query->whereBetween($column, is_array($value) ? $value : [$value, $value]),
            'whereIn' => $query->whereIn($column, (array) $value),
            'whereNotIn' => $query->whereNotIn($column, (array) $value),
            'whereNull' => $query->whereNull($column),
            'whereNotNull' => $query->whereNotNull($column),
            'whereDate' => $query->whereDate($column, $operator, $value),
            'orWhere' => $query->orWhere($column, $operator, $value),
            default => $query->where($column, $operator, $value),
        };
    }

    protected function pivotData($rawData, array $rows, array $columns, array $values): array
    {
        if (empty($rows) || empty($columns) || empty($values)) {
            return [];
        }

        $rowField = $rows[0];
        $colField = $columns[0];
        $valueDef = $values[0];
        $valueField = $valueDef['field'] ?? null;
        $aggregate = $valueDef['aggregate'] ?? 'sum';

        $rowLabels = collect($rawData)->pluck($rowField)->unique()->values();
        $colLabels = collect($rawData)->pluck($colField)->unique()->values();

        $matrix = [];
        $rowTotals = [];
        $colTotals = array_fill_keys($colLabels->toArray(), 0);
        $grandTotal = 0;

        foreach ($rowLabels as $rLabel) {
            $matrix[$rLabel] = [];
            $rTotal = 0;

            foreach ($colLabels as $cLabel) {
                $filtered = collect($rawData)->where($rowField, $rLabel)->where($colField, $cLabel);
                $val = match ($aggregate) {
                    'sum' => $filtered->sum($valueField),
                    'avg' => $filtered->avg($valueField),
                    'count' => $filtered->count(),
                    'min' => $filtered->min($valueField),
                    'max' => $filtered->max($valueField),
                    default => $filtered->sum($valueField),
                };

                $val = (float) $val;
                $matrix[$rLabel][$cLabel] = $val;
                $rTotal += $val;
                $colTotals[$cLabel] += $val;
            }

            $rowTotals[$rLabel] = $rTotal;
            $grandTotal += $rTotal;
        }

        return [
            'row_labels' => $rowLabels,
            'col_labels' => $colLabels,
            'matrix' => $matrix,
            'row_totals' => $rowTotals,
            'col_totals' => $colTotals,
            'grand_total' => $grandTotal,
        ];
    }

    protected function evaluateExpression(string $expression): float
    {
        $expression = preg_replace('/[^0-9+\-*\/\(\)\.]/', '', $expression);

        return eval("return {$expression};");
    }
}

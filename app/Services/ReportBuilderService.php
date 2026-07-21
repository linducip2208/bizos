<?php

namespace App\Services;

use App\Models\ReportSchedule;
use App\Models\ReportSnapshot;
use App\Models\ReportTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportBuilderService
{
    public function buildQuery(ReportTemplate $template, array $params = []): Builder
    {
        $config = $template->query_config;

        if (!$config || empty($config['table_name'])) {
            throw new \RuntimeException('Report template has no valid query configuration.');
        }

        $tableName = $config['table_name'];
        $query = DB::table($tableName);

        if (!empty($config['select'])) {
            $query->select(DB::raw(implode(', ', $config['select'])));
        }

        if (!empty($config['joins'])) {
            foreach ($config['joins'] as $join) {
                $joinType = $join['type'] ?? 'inner';
                $query->join(
                    $join['table'],
                    $join['first'],
                    $join['operator'] ?? '=',
                    $join['second'],
                    $joinType
                );
            }
        }

        foreach ($params as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where($key, $value);
            }
        }

        if (!empty($config['filters'])) {
            foreach ($config['filters'] as $filter) {
                $this->applyFilter($query, $filter);
            }
        }

        if (!empty($config['group_by'])) {
            $query->groupBy($config['group_by']);
        }

        if (!empty($config['having'])) {
            foreach ($config['having'] as $having) {
                $query->having(DB::raw($having));
            }
        }

        if (!empty($config['sort'])) {
            foreach ($config['sort'] as $sort) {
                $query->orderBy($sort['column'], $sort['direction'] ?? 'asc');
            }
        }

        if (isset($config['limit'])) {
            $query->limit((int) $config['limit']);
        }

        return $query;
    }

    public function execute(ReportTemplate $template, array $params = []): Collection
    {
        $query = $this->buildQuery($template, $params);

        return collect($query->get());
    }

    public function generateChartData(ReportTemplate $template, array $params = []): array
    {
        $data = $this->execute($template, $params);
        $chartConfig = $template->chart_config;

        if (!$chartConfig) {
            return $this->chartDataFromQueryConfig($template, $data);
        }

        $type = $chartConfig['type'] ?? 'bar';
        $xAxis = $chartConfig['x_axis'] ?? null;
        $yAxis = $chartConfig['y_axis'] ?? null;
        $colors = $chartConfig['colors'] ?? ['#4f46e5', '#7c3aed', '#2563eb', '#059669', '#d97706'];

        $labels = [];
        $datasets = [];

        if ($xAxis && $yAxis) {
            if (is_array($yAxis)) {
                foreach ($yAxis as $idx => $yKey) {
                    $dataset = [
                        'label' => is_string($yKey) ? $yKey : ($yKey['label'] ?? "Series {$idx}"),
                        'data' => [],
                        'backgroundColor' => $colors[$idx % count($colors)] ?? '#4f46e5',
                        'borderColor' => $colors[$idx % count($colors)] ?? '#4f46e5',
                    ];

                    $yField = is_string($yKey) ? $yKey : ($yKey['field'] ?? $yKey);
                    foreach ($data as $row) {
                        $row = (array) $row;
                        $labels[] = $row[$xAxis] ?? '';
                        $dataset['data'][] = (float) ($row[$yField] ?? 0);
                    }
                    $labels = array_unique($labels);
                    $labels = array_values($labels);
                    $dataset['data'] = array_values(array_slice($dataset['data'], 0, count($labels)));
                    $datasets[] = $dataset;
                }
            } else {
                foreach ($data as $row) {
                    $row = (array) $row;
                    $labels[] = $row[$xAxis] ?? '';
                }

                $datasets[] = [
                    'label' => $template->name,
                    'data' => $data->pluck($yAxis)->map(fn($v) => (float) $v)->toArray(),
                    'backgroundColor' => $colors,
                    'borderColor' => $colors[0] ?? '#4f46e5',
                ];
            }
        }

        return [
            'type' => $type,
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    public function exportToExcel(ReportTemplate $template, array $params = []): BinaryFileResponse
    {
        $data = $this->execute($template, $params);
        $fileName = Str::slug($template->name) . '_' . now()->format('Ymd_His') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($data->isNotEmpty()) {
            $headers = array_keys((array) $data->first());
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', ucwords(str_replace('_', ' ', $header)));
                $col++;
            }

            $row = 2;
            foreach ($data as $record) {
                $record = (array) $record;
                $col = 'A';
                foreach ($record as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }
        }

        $tempPath = storage_path('app/temp/' . $fileName);
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend();
    }

    public function exportToPdf(ReportTemplate $template, array $params = []): BinaryFileResponse
    {
        $data = $this->execute($template, $params);
        $chartData = $this->generateChartData($template, $params);
        $fileName = Str::slug($template->name) . '_' . now()->format('Ymd_His') . '.pdf';

        $html = view('reports.pdf-template', [
            'template' => $template,
            'data' => $data,
            'chartData' => $chartData,
            'generatedAt' => now()->format('d M Y H:i'),
        ])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'landscape');

        $tempPath = storage_path('app/temp/' . $fileName);
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        $pdf->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend();
    }

    public function exportToCsv(ReportTemplate $template, array $params = []): BinaryFileResponse
    {
        $data = $this->execute($template, $params);
        $fileName = Str::slug($template->name) . '_' . now()->format('Ymd_His') . '.csv';

        $tempPath = storage_path('app/temp/' . $fileName);
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $handle = fopen($tempPath, 'w');

        if ($data->isNotEmpty()) {
            fputcsv($handle, array_keys((array) $data->first()));
            foreach ($data as $record) {
                fputcsv($handle, (array) $record);
            }
        }

        fclose($handle);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend();
    }

    public function generateReport(ReportTemplate $template, array $params = [], ?string $format = 'pdf'): ReportSnapshot
    {
        $data = $this->execute($template, $params);

        $snapshot = ReportSnapshot::create([
            'report_template_id' => $template->id,
            'generated_by' => auth()->id(),
            'snapshot_data' => $data->toArray(),
            'format' => $format,
            'file_path' => null,
            'file_size' => 0,
            'created_at' => now(),
        ]);

        return $snapshot;
    }

    public function scheduleReport(ReportTemplate $template, array $config): ReportSchedule
    {
        return ReportSchedule::create(array_merge($config, [
            'report_template_id' => $template->id,
        ]));
    }

    protected function applyFilter($query, array $filter): void
    {
        $column = $filter['column'] ?? null;
        $operator = $filter['operator'] ?? '=';
        $value = $filter['value'] ?? null;
        $type = $filter['type'] ?? 'where';

        if (!$column) {
            return;
        }

        if ($type === 'whereIn' && is_array($value)) {
            $query->whereIn($column, $value);
        } elseif ($type === 'whereNotIn' && is_array($value)) {
            $query->whereNotIn($column, $value);
        } elseif ($type === 'whereBetween' && is_array($value) && count($value) === 2) {
            $query->whereBetween($column, $value);
        } elseif ($type === 'whereNull') {
            $query->whereNull($column);
        } elseif ($type === 'whereNotNull') {
            $query->whereNotNull($column);
        } elseif ($type === 'whereDate') {
            $query->whereDate($column, $operator, $value);
        } elseif ($type === 'whereYear') {
            $query->whereYear($column, $operator, $value);
        } elseif ($type === 'whereMonth') {
            $query->whereMonth($column, $operator, $value);
        } elseif ($type === 'orWhere') {
            $query->orWhere($column, $operator, $value);
        } else {
            $query->where($column, $operator, $value);
        }
    }

    protected function chartDataFromQueryConfig(ReportTemplate $template, Collection $data): array
    {
        if ($data->isEmpty()) {
            return ['type' => 'bar', 'labels' => [], 'datasets' => []];
        }

        $keys = array_keys((array) $data->first());
        $labelKey = $keys[0] ?? 'label';
        $valueKey = $keys[1] ?? 'value';

        $labels = $data->pluck($labelKey)->toArray();
        $values = $data->pluck($valueKey)->map(fn($v) => (float) $v)->toArray();

        return [
            'type' => $template->chart_config['type'] ?? 'bar',
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $template->name,
                    'data' => $values,
                    'backgroundColor' => $template->chart_config['colors'] ?? ['#4f46e5'],
                    'borderColor' => ($template->chart_config['colors'] ?? ['#4f46e5'])[0],
                ],
            ],
        ];
    }
}

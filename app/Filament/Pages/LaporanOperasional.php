<?php

namespace App\Filament\Pages;

use App\Models\ReportTemplate;
use App\Services\ReportBuilderService;
use App\Services\ReportNlgService;
use Filament\Pages\Page;

class LaporanOperasional extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 1103;

    protected string $view = 'filament.pages.laporan-operasional';

    protected static ?string $title = 'Laporan Operasional';

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan';
    }

    public array $cards = [];
    public array $attendanceLabels = [];
    public array $attendanceData = [];
    public array $projectStatusLabels = [];
    public array $projectStatusData = [];
    public array $topPerformers = [];
    public string $dateFrom;
    public string $dateTo;
    public string $groupBy = 'harian';
    public ?ReportTemplate $activeTemplate = null;
    public array $availableTemplates = [];
    public string $nlgSummary = '';

    public function mount(): void
    {
        $this->dateFrom = request('date_from', now()->startOfMonth()->format('Y-m-d'));
        $this->dateTo = request('date_to', now()->format('Y-m-d'));
        $this->groupBy = request('group_by', 'harian');

        $this->availableTemplates = ReportTemplate::where('category', 'hrm')
            ->where(function ($q) {
                $q->where('is_public', true)
                    ->orWhere('company_id', auth()->user()->company_id);
            })
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->activeTemplate = request('template_id')
            ? ReportTemplate::find(request('template_id'))
            : ReportTemplate::where('slug', 'attendance-summary')->first();

        if ($this->activeTemplate) {
            $this->loadFromTemplate();
        } else {
            $this->loadLegacyData();
        }

        $this->generateNlgSummary();
    }

    protected function generateNlgSummary(): void
    {
        try {
            $nlg = app(ReportNlgService::class);

            $reportData = [
                'module' => 'Operasional',
                'periode' => "{$this->dateFrom} s/d {$this->dateTo}",
                'summary_cards' => $this->cards,
                'top_performers' => array_slice($this->topPerformers, 0, 5),
            ];

            $this->nlgSummary = $nlg->generateExecutiveSummary($reportData, 'operasional');
        } catch (\Exception $e) {
            $this->nlgSummary = 'Ringkasan AI tidak tersedia. Menampilkan data numerik.';
        }
    }

    protected function loadFromTemplate(): void
    {
        $service = app(ReportBuilderService::class);

        try {
            $params = ['date_from' => $this->dateFrom, 'date_to' => $this->dateTo, 'group_by' => $this->groupBy];
            $data = $service->execute($this->activeTemplate, $params);
            $chartData = $service->generateChartData($this->activeTemplate, $params);

            $this->cards = [];
            foreach ($data as $row) {
                $row = (array) $row;
                foreach ($row as $key => $value) {
                    if (is_numeric($value)) {
                        $this->cards[$key] = round((float) ($this->cards[$key] ?? 0) + (float) $value, 1);
                    }
                }
            }

            $this->attendanceLabels = $chartData['labels'] ?? [];
            $this->attendanceData = ($chartData['datasets'][0]['data'] ?? []);
            $this->topPerformers = $data->toArray();

            $this->projectStatusLabels = [];
            $this->projectStatusData = [];
        } catch (\Exception $e) {
            $this->loadLegacyData();
        }
    }

    protected function loadLegacyData(): void
    {
        $totalEmployees = \App\Models\Employee::where('status', 'active')->count();
        $workingDays = $this->calculateWorkingDays($this->dateFrom, $this->dateTo);
        $totalPossibleAttendance = $totalEmployees * $workingDays;

        $actualAttendance = \App\Models\Attendance::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->whereNotNull('clock_in')->count();

        $kehadiranRate = $totalPossibleAttendance > 0
            ? ($actualAttendance / $totalPossibleAttendance) * 100 : 0;

        $overtimeMinutes = \App\Models\Overtime::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->where('status', 'approved')->sum('duration_minutes');
        $overtimeCount = \App\Models\Overtime::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->where('status', 'approved')->count();
        $rataOvertime = $overtimeCount > 0 ? ($overtimeMinutes / $overtimeCount) / 60 : 0;

        $cutiTerpakai = \App\Models\Leave::whereBetween('start_date', [$this->dateFrom, $this->dateTo])
            ->where('status', 'approved')->sum('total_days');

        $totalProjects = \App\Models\Project::count();
        $completedProjects = \App\Models\Project::where('status', 'completed')->count();
        $completionRate = $totalProjects > 0 ? ($completedProjects / $totalProjects) * 100 : 0;

        $this->cards = [
            'kehadiran_rate' => round($kehadiranRate, 1),
            'rata_overtime' => round($rataOvertime, 1),
            'cuti_terpakai' => $cutiTerpakai,
            'completion_rate' => round($completionRate, 1),
        ];

        $this->loadAttendanceTrend();
        $this->loadProjectStatusChart();
        $this->loadTopPerformers();
    }

    protected function calculateWorkingDays(string $from, string $to): int
    {
        $start = \Carbon\Carbon::parse($from);
        $end = \Carbon\Carbon::parse($to);
        $days = 0;
        while ($start->lte($end)) {
            if (!$start->isWeekend()) $days++;
            $start->addDay();
        }
        return max($days, 1);
    }

    protected function loadAttendanceTrend(): void
    {
        $groupFormat = match ($this->groupBy) {
            'harian' => '%Y-%m-%d',
            'mingguan' => '%Y-%u',
            default => '%Y-%m',
        };

        $records = \App\Models\Attendance::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->selectRaw("DATE_FORMAT(date, '{$groupFormat}') as period, COUNT(*) as count")
            ->whereNotNull('clock_in')
            ->groupBy('period')->orderBy('period')->get();

        $this->attendanceLabels = $records->pluck('period')->map(function ($p) {
            if ($this->groupBy === 'bulanan') {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $p);
                return $date->translatedFormat('M Y');
            }
            if ($this->groupBy === 'mingguan') {
                $parts = explode('-', $p);
                $year = $parts[0];
                $week = (int) ($parts[1] ?? 1);
                return 'Mgg ke-' . $week . ', ' . $year;
            }
            return $p;
        })->toArray();
        $this->attendanceData = $records->pluck('count')->map(fn($v) => (int) $v)->toArray();
    }

    protected function loadProjectStatusChart(): void
    {
        $statuses = \App\Models\Project::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')->get();

        $labels = [];
        $data = [];
        foreach ($statuses as $s) {
            $label = match ($s->status) {
                'active' => 'Aktif', 'completed' => 'Selesai',
                'on_hold' => 'Ditunda', 'cancelled' => 'Dibatalkan',
                default => ucfirst($s->status ?? 'Draft'),
            };
            $labels[] = $label;
            $data[] = (int) $s->count;
        }
        $this->projectStatusLabels = $labels;
        $this->projectStatusData = $data;
    }

    protected function loadTopPerformers(): void
    {
        $performers = \App\Models\Timesheet::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->where('status', 'approved')
            ->selectRaw('employee_id, SUM(total_hours) as total_jam')
            ->groupBy('employee_id')->orderByDesc('total_jam')->limit(10)
            ->with('employee')->get();

        $this->topPerformers = $performers->map(function ($ts) {
            return [
                'name' => $ts->employee?->first_name . ' ' . $ts->employee?->last_name,
                'department' => $ts->employee?->department?->name ?? '-',
                'total_jam' => (float) $ts->total_jam,
            ];
        })->toArray();
    }

    public function getExportPdfUrl(): string
    {
        return route('laporan.operasional.pdf', request()->only(['date_from', 'date_to', 'group_by', 'template_id']));
    }

    public function getExportCsvUrl(): string
    {
        return route('laporan.operasional.csv', request()->only(['date_from', 'date_to', 'group_by', 'template_id']));
    }

    public function exportPdf()
    {
        $this->mount();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-operasional', [
            'title' => 'Laporan Operasional',
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'groupBy' => $this->groupBy,
            'summaryCards' => $this->cards,
            'topPerformers' => $this->topPerformers,
        ]);

        return $pdf->download('laporan-operasional-' . now()->format('Ymd') . '.pdf');
    }

    public function exportCsv()
    {
        $this->mount();

        $filename = 'laporan-operasional-' . now()->format('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['#', 'Nama', 'Department', 'Total Jam']);

            $i = 1;
            foreach ($this->topPerformers as $p) {
                fputcsv($handle, [
                    $i++,
                    $p['name'] ?? '-',
                    $p['department'] ?? '-',
                    $p['total_jam'] ?? 0,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

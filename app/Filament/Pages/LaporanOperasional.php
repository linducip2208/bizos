<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Project;
use App\Models\Timesheet;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class LaporanOperasional extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

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

    public function mount(): void
    {
        $this->dateFrom = request('date_from', now()->startOfMonth()->format('Y-m-d'));
        $this->dateTo = request('date_to', now()->format('Y-m-d'));

        $this->loadData();
    }

    protected function loadData(): void
    {
        $totalEmployees = Employee::where('status', 'active')->count();

        $workingDays = $this->calculateWorkingDays($this->dateFrom, $this->dateTo);
        $totalPossibleAttendance = $totalEmployees * $workingDays;

        $actualAttendance = Attendance::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->whereNotNull('clock_in')
            ->count();

        $kehadiranRate = $totalPossibleAttendance > 0
            ? ($actualAttendance / $totalPossibleAttendance) * 100
            : 0;

        $overtimeMinutes = Overtime::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->where('status', 'approved')
            ->sum('duration_minutes');

        $overtimeCount = Overtime::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->where('status', 'approved')
            ->count();

        $rataOvertime = $overtimeCount > 0
            ? ($overtimeMinutes / $overtimeCount) / 60
            : 0;

        $cutiTerpakai = Leave::whereBetween('start_date', [$this->dateFrom, $this->dateTo])
            ->where('status', 'approved')
            ->sum('total_days');

        $totalProjects = Project::count();
        $completedProjects = Project::where('status', 'completed')->count();
        $completionRate = $totalProjects > 0
            ? ($completedProjects / $totalProjects) * 100
            : 0;

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
            if (!$start->isWeekend()) {
                $days++;
            }
            $start->addDay();
        }

        return max($days, 1);
    }

    protected function loadAttendanceTrend(): void
    {
        $records = Attendance::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->selectRaw("DATE_FORMAT(date, '%Y-%m-%d') as day, COUNT(*) as count")
            ->whereNotNull('clock_in')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $this->attendanceLabels = $records->pluck('day')->toArray();
        $this->attendanceData = $records->pluck('count')->map(fn ($v) => (int) $v)->toArray();
    }

    protected function loadProjectStatusChart(): void
    {
        $statuses = Project::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $labels = [];
        $data = [];

        foreach ($statuses as $s) {
            $label = match ($s->status) {
                'active' => 'Aktif',
                'completed' => 'Selesai',
                'on_hold' => 'Ditunda',
                'cancelled' => 'Dibatalkan',
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
        $performers = Timesheet::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->where('status', 'approved')
            ->selectRaw('employee_id, SUM(total_hours) as total_jam')
            ->groupBy('employee_id')
            ->orderByDesc('total_jam')
            ->limit(10)
            ->with('employee')
            ->get();

        $this->topPerformers = $performers->map(function ($ts) {
            return [
                'name' => $ts->employee?->first_name . ' ' . $ts->employee?->last_name,
                'department' => $ts->employee?->department?->name ?? '-',
                'total_jam' => (float) $ts->total_jam,
            ];
        })->toArray();
    }
}

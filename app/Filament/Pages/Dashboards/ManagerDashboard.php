<?php

namespace App\Filament\Pages\Dashboards;

use App\Models\ApprovalRequest;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Project;
use App\Models\Task;
use Filament\Pages\Page;

class ManagerDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.dashboards.manager';

    protected static ?string $title = 'Dashboard Manajer';

    protected static ?string $navigationLabel = 'Dashboard Manajer';

    protected static ?string $slug = 'manager-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return "\u{1F3E0} Dashboard";
    }

    public int $attendanceToday = 0;
    public int $attendanceLate = 0;
    public int $attendanceAbsent = 0;
    public int $pendingApprovals = 0;
    public array $approvalItems = [];
    public int $tasksDueThisWeek = 0;
    public int $tasksOverdue = 0;
    public int $tasksCompleted = 0;
    public array $teamPerformance = [];
    public int $pendingLeaves = 0;
    public array $recentLeaves = [];
    public array $projectStatus = [];
    public int $teamSize = 0;
    public array $attendanceChartLabels = [];
    public array $attendanceChartData = [];

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $user = auth()->user();
        $employee = $user->employee;
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $managedDepartmentIds = [];
        if ($employee?->department_id) {
            $managedDepartmentIds[] = $employee->department_id;
        }

        $this->loadAttendanceData($companyId, $employee, $today);
        $this->loadApprovalData($companyId, $employee);
        $this->loadTaskData($companyId, $employee, $weekStart, $weekEnd);
        $this->loadLeaveData($companyId, $employee);
        $this->loadProjectData($companyId, $employee);
        $this->teamSize = $this->getTeamSize($companyId, $employee);
        $this->loadAttendanceChart($companyId, $employee);
        $this->loadTeamPerformance($companyId, $employee);
    }

    protected function loadAttendanceData(int $companyId, $employee, string $today): void
    {
        $employeeIds = $this->getTeamEmployeeIds($companyId, $employee);

        $this->attendanceToday = Attendance::whereIn('employee_id', $employeeIds)
            ->whereDate('date', $today)
            ->whereNotNull('clock_in')
            ->count();

        $this->attendanceLate = Attendance::whereIn('employee_id', $employeeIds)
            ->whereDate('date', $today)
            ->where('late_minutes', '>', 0)
            ->count();

        $teamCount = count($employeeIds);
        $this->attendanceAbsent = max(0, $teamCount - $this->attendanceToday);
    }

    protected function loadApprovalData(int $companyId, $employee): void
    {
        $employeeIds = $this->getTeamEmployeeIds($companyId, $employee);

        $this->pendingApprovals = ApprovalRequest::where('company_id', $companyId)
            ->where('status', 'pending')
            ->whereIn('requester_id', $employeeIds)
            ->count();

        $this->approvalItems = ApprovalRequest::with('requester')
            ->where('company_id', $companyId)
            ->where('status', 'pending')
            ->whereIn('requester_id', $employeeIds)
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'module' => $a->module,
                'requester_name' => $a->requester?->first_name . ' ' . $a->requester?->last_name,
                'created_at' => $a->created_at?->diffForHumans(),
            ])
            ->toArray();
    }

    protected function loadTaskData(int $companyId, $employee, $weekStart, $weekEnd): void
    {
        $employeeIds = $this->getTeamEmployeeIds($companyId, $employee);

        $this->tasksDueThisWeek = Task::whereHas('assignees', function ($q) use ($employeeIds) {
            $q->whereIn('employee_id', $employeeIds);
        })->whereBetween('due_date', [$weekStart, $weekEnd])
            ->whereNotIn('status', ['done', 'cancelled'])
            ->count();

        $this->tasksOverdue = Task::whereHas('assignees', function ($q) use ($employeeIds) {
            $q->whereIn('employee_id', $employeeIds);
        })->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['done', 'cancelled'])
            ->count();

        $this->tasksCompleted = Task::whereHas('assignees', function ($q) use ($employeeIds) {
            $q->whereIn('employee_id', $employeeIds);
        })->where('status', 'done')
            ->whereBetween('completed_at', [$weekStart, $weekEnd])
            ->count();
    }

    protected function loadLeaveData(int $companyId, $employee): void
    {
        $employeeIds = $this->getTeamEmployeeIds($companyId, $employee);

        $this->pendingLeaves = Leave::whereIn('employee_id', $employeeIds)
            ->where('status', 'pending')
            ->count();

        $this->recentLeaves = Leave::with(['employee', 'leaveType'])
            ->whereIn('employee_id', $employeeIds)
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'employee_name' => $l->employee?->first_name . ' ' . $l->employee?->last_name,
                'leave_type' => $l->leaveType?->name ?? 'Cuti',
                'start_date' => $l->start_date?->format('d M'),
                'end_date' => $l->end_date?->format('d M'),
                'total_days' => $l->total_days,
            ])
            ->toArray();
    }

    protected function loadProjectData(int $companyId, $employee): void
    {
        $this->projectStatus = Project::where('company_id', $companyId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->map(fn ($p) => [
                'status' => $p->status,
                'count' => $p->count,
            ])
            ->toArray();
    }

    protected function loadTeamPerformance(int $companyId, $employee): void
    {
        $employeeIds = $this->getTeamEmployeeIds($companyId, $employee);

        $this->teamPerformance = Employee::whereIn('id', $employeeIds)
            ->withCount(['taskAssignees as completed_tasks' => function ($q) {
                $q->whereHas('task', function ($q2) {
                    $q2->where('status', 'done');
                });
            }])
            ->withCount(['taskAssignees as total_tasks' => function ($q) {
                $q->whereHas('task', function ($q2) {
                    $q2->whereNotIn('status', ['cancelled']);
                });
            }])
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->first_name . ' ' . $e->last_name,
                'position' => $e->position?->name ?? '-',
                'completed_tasks' => $e->completed_tasks,
                'total_tasks' => $e->total_tasks,
            ])
            ->toArray();
    }

    protected function getTeamEmployeeIds(int $companyId, $employee): array
    {
        if (!$employee) return [];

        $query = Employee::where('company_id', $companyId)->where('status', 'active');

        if ($employee->department_id) {
            $query->where('department_id', $employee->department_id);
        }

        return $query->pluck('id')->toArray();
    }

    protected function getTeamSize(int $companyId, $employee): int
    {
        if (!$employee) return 0;

        $query = Employee::where('company_id', $companyId)->where('status', 'active');

        if ($employee->department_id) {
            $query->where('department_id', $employee->department_id);
        }

        return $query->count();
    }

    protected function loadAttendanceChart(int $companyId, $employee): void
    {
        $days = collect(range(0, 6))->map(function ($i) {
            return [
                'label' => now()->subDays($i)->translatedFormat('D'),
                'date' => now()->subDays($i)->toDateString(),
            ];
        })->reverse()->values();

        $employeeIds = $this->getTeamEmployeeIds($companyId, $employee);

        $this->attendanceChartLabels = $days->pluck('label')->toArray();
        $this->attendanceChartData = $days->map(function ($d) use ($employeeIds) {
            return Attendance::whereIn('employee_id', $employeeIds)
                ->whereDate('date', $d['date'])
                ->whereNotNull('clock_in')
                ->count();
        })->toArray();
    }
}

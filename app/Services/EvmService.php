<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimesheetEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EvmService
{
    public function calculateEvm(Project $project): array
    {
        $bac = (float) $project->budget;
        $pv = $this->calculatePlannedValue($project);
        $ev = $this->calculateEarnedValue($project);
        $ac = $this->calculateActualCost($project);

        $spi = $pv > 0 ? round($ev / $pv, 4) : ($ev > 0 ? 1.0 : 0);
        $cpi = $ac > 0 ? round($ev / $ac, 4) : ($ev > 0 ? 1.0 : 0);
        $eac = $cpi > 0 ? round($bac / $cpi, 2) : $bac;
        $etc = round($eac - $ac, 2);
        $vac = round($bac - $eac, 2);

        $scheduleStatus = $spi >= 0.95 ? 'on_schedule' : ($spi >= 0.85 ? 'at_risk' : 'behind');
        $costStatus = $cpi >= 0.95 ? 'on_budget' : ($cpi >= 0.85 ? 'at_risk' : 'over_budget');

        return [
            'bac' => $bac,
            'pv' => round($pv, 2),
            'ev' => round($ev, 2),
            'ac' => round($ac, 2),
            'spi' => $spi,
            'cpi' => $cpi,
            'eac' => $eac,
            'etc' => $etc,
            'vac' => $vac,
            'schedule_variance' => round($ev - $pv, 2),
            'cost_variance' => round($ev - $ac, 2),
            'schedule_status' => $scheduleStatus,
            'cost_status' => $costStatus,
            'percent_complete' => $bac > 0 ? round(($ev / $bac) * 100, 2) : 0,
            'percent_spent' => $bac > 0 ? round(($ac / $bac) * 100, 2) : 0,
            'calculated_at' => now()->toIso8601String(),
        ];
    }

    public function getEvmTrend(Project $project, int $months = 6): array
    {
        $trend = [];
        $now = Carbon::now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $endOfMonth = $date->copy()->endOfMonth();

            $pv = $this->calculatePlannedValueAtDate($project, $endOfMonth);
            $ev = $this->calculateEarnedValueAtDate($project, $endOfMonth);
            $ac = $this->calculateActualCostAtDate($project, $endOfMonth);

            $trend[] = [
                'month' => $date->format('Y-m'),
                'label' => $date->translatedFormat('M Y'),
                'pv' => round($pv, 2),
                'ev' => round($ev, 2),
                'ac' => round($ac, 2),
            ];
        }

        return $trend;
    }

    public function getProjectPerformanceIndicators(Project $project): array
    {
        $evm = $this->calculateEvm($project);
        $tasks = Task::where('project_id', $project->id)->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'done')->count();
        $overdueTasks = $tasks->filter(function ($task) {
            return $task->due_date && $task->due_date < today() && $task->status !== 'done';
        })->count();

        return [
            'evm' => $evm,
            'task_metrics' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
                'overdue_tasks' => $overdueTasks,
            ],
            'budget_utilization' => $evm['percent_spent'],
            'schedule_performance' => $evm['spi'],
            'cost_performance' => $evm['cpi'],
            'estimated_completion' => $evm['eac'],
        ];
    }

    protected function calculatePlannedValue(Project $project): float
    {
        return $this->calculatePlannedValueAtDate($project, now());
    }

    protected function calculatePlannedValueAtDate(Project $project, Carbon $asOf): float
    {
        $startDate = Carbon::parse($project->start_date);
        $endDate = Carbon::parse($project->end_date);
        $totalDuration = max($startDate->diffInDays($endDate), 1);
        $elapsed = max($startDate->diffInDays(min($asOf, $endDate)), 0);

        $progressRatio = min($elapsed / $totalDuration, 1.0);
        return (float) $project->budget * $progressRatio;
    }

    protected function calculateEarnedValue(Project $project): float
    {
        return $this->calculateEarnedValueAtDate($project, now());
    }

    protected function calculateEarnedValueAtDate(Project $project, Carbon $asOf): float
    {
        $tasks = Task::where('project_id', $project->id)
            ->where(function ($q) use ($asOf) {
                $q->whereNull('completed_at')
                    ->orWhere('completed_at', '<=', $asOf);
            })
            ->get();

        $totalHours = $tasks->sum('estimated_hours');
        if ($totalHours <= 0) return 0;

        $budget = (float) $project->budget;
        $ev = 0;

        foreach ($tasks as $task) {
            $weight = ((float) $task->estimated_hours) / $totalHours;
            $taskBudget = $budget * $weight;
            $completionPercent = $this->getTaskCompletionPercent($task);

            if ($task->completed_at && Carbon::parse($task->completed_at)->lte($asOf)) {
                $completionPercent = 100;
            }

            $ev += $taskBudget * ($completionPercent / 100);
        }

        return $ev;
    }

    protected function calculateActualCost(Project $project): float
    {
        return $this->calculateActualCostAtDate($project, now());
    }

    protected function calculateActualCostAtDate(Project $project, Carbon $asOf): float
    {
        $taskIds = Task::where('project_id', $project->id)->pluck('id');

        $entries = TimesheetEntry::whereIn('task_id', $taskIds)
            ->where('is_billable', true)
            ->whereHas('timesheet', function ($q) use ($asOf) {
                $q->where('date', '<=', $asOf->toDateString());
            })
            ->get();

        $ac = 0;
        foreach ($entries as $entry) {
            $employee = $entry->timesheet?->employee;
            $rate = $employee?->hourly_rate ?? 0;
            $ac += (float) $entry->hours * (float) $rate;
        }

        if ($ac == 0 && (float) $project->actual_cost > 0) {
            $ac = (float) $project->actual_cost;
        }

        return $ac;
    }

    protected function getTaskCompletionPercent(Task $task): float
    {
        if ($task->status === 'done') return 100;
        if ($task->status === 'in_progress') return 50;
        if ($task->estimated_hours > 0 && $task->actual_hours > 0) {
            return min(round(((float) $task->actual_hours / (float) $task->estimated_hours) * 100, 1), 99);
        }
        return 0;
    }
}

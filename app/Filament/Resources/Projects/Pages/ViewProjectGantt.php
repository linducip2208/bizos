<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\TaskDependency;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;

class ViewProjectGantt extends Page
{
    protected static string $resource = ProjectResource::class;

    protected string $view = 'filament.pages.project-gantt';

    protected static ?string $title = 'Gantt Chart';

    protected static ?int $navigationSort = 503;

    public Project $project;
    public array $tasks = [];
    public array $milestones = [];
    public array $dependencies = [];
    public string $viewMode = 'Week';

    public function mount(int | string $record): void
    {
        $this->project = Project::with(['tasks', 'milestones', 'tasks.dependencies', 'tasks.children'])->findOrFail($record);
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->tasks = $this->project->tasks()
            ->whereNull('parent_id')
            ->with(['children', 'dependencies', 'assignees'])
            ->orderBy('sort_order')
            ->orderBy('start_date')
            ->get()
            ->map(function ($task) {
                return $this->formatTask($task);
            })
            ->toArray();

        $this->milestones = $this->project->milestones()
            ->orderBy('target_date')
            ->get()
            ->map(function ($ms) {
                return [
                    'id' => $ms->id,
                    'name' => $ms->name,
                    'target_date' => $ms->target_date?->format('Y-m-d'),
                    'status' => $ms->status,
                ];
            })
            ->toArray();

        $this->dependencies = TaskDependency::whereIn('task_id', $this->project->tasks()->pluck('id'))
            ->with(['task', 'dependsOnTask'])
            ->get()
            ->map(function ($dep) {
                return [
                    'id' => $dep->id,
                    'task_id' => $dep->task_id,
                    'depends_on_task_id' => $dep->depends_on_task_id,
                    'type' => $dep->dependency_type ?? 'FS',
                ];
            })
            ->toArray();
    }

    public function updateTaskDate(int $taskId, string $startDate, string $endDate): void
    {
        $task = Task::findOrFail($taskId);
        $task->update([
            'start_date' => $startDate,
            'due_date' => $endDate,
        ]);

        $this->loadData();

        Notification::make()
            ->title('Tanggal tugas diperbarui')
            ->body("Tugas \"{$task->title}\" dijadwalkan ulang")
            ->success()
            ->send();
    }

    protected function formatTask(Task $task): array
    {
        $progress = 0;
        if ($task->status === 'done') {
            $progress = 100;
        } elseif ($task->estimated_hours > 0 && $task->actual_hours > 0) {
            $progress = min(100, round(((float) $task->actual_hours / (float) $task->estimated_hours) * 100, 1));
        }

        $data = [
            'id' => (string) $task->id,
            'name' => $task->title,
            'start' => $task->start_date?->format('Y-m-d') ?? $this->project->start_date?->format('Y-m-d') ?? date('Y-m-d'),
            'end' => $task->due_date?->format('Y-m-d') ?? date('Y-m-d', strtotime('+7 days')),
            'progress' => $progress,
            'custom_class' => match ($task->status) {
                'done' => 'bar-green',
                'in_progress' => 'bar-blue',
                'review' => 'bar-orange',
                default => 'bar-gray',
            },
            'dependencies' => $task->dependencies->pluck('depends_on_task_id')->map(fn($id) => (string) $id)->toArray(),
            'assignees' => $task->assignees->map(fn($a) => $a->first_name)->implode(', '),
        ];

        if ($task->children->isNotEmpty()) {
            $data['children'] = $task->children->map(fn($child) => $this->formatTask($child))->toArray();
            $subtasks = $task->children;
            $sumProgress = $subtasks->sum(function ($st) {
                return $this->formatTask($st)['progress'];
            });
            $data['progress'] = $subtasks->count() > 0 ? round($sumProgress / $subtasks->count(), 1) : 0;
        }

        return $data;
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    protected function getViewData(): array
    {
        return [
            'project' => $this->project,
            'tasks' => $this->tasks,
            'milestones' => $this->milestones,
            'dependencies' => $this->dependencies,
            'viewMode' => $this->viewMode,
            'today' => date('Y-m-d'),
            'projectStart' => $this->project->start_date?->format('Y-m-d') ?? date('Y-m-d'),
            'projectEnd' => $this->project->end_date?->format('Y-m-d') ?? date('Y-m-d', strtotime('+30 days')),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Project';
    }
}
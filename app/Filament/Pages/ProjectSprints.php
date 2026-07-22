<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\SprintTask;
use App\Models\Task;
use Filament\Pages\Page;

class ProjectSprints extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

    protected static ?int $navigationSort = 906;

    protected string $view = 'filament.pages.project-sprints';

    protected static ?string $title = 'Sprints';

    protected static ?string $navigationLabel = 'Sprints';

    protected static ?string $slug = 'project-sprints';

    public ?Project $project = null;
    public ?Sprint $activeSprint = null;
    public array $velocityData = [];
    public array $burndownData = [];

    public static function getNavigationGroup(): ?string
    {
        return '📋 Project & Work';
    }

    public function mount(): void
    {
        $projectId = request('project_id');
        $this->project = $projectId ? Project::find($projectId) : Project::latest()->first();

        if ($this->project) {
            $this->loadData();
        }
    }

    public function loadData(): void
    {
        if (!$this->project) return;

        $this->activeSprint = Sprint::where('project_id', $this->project->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $this->velocityData = Sprint::where('project_id', $this->project->id)
            ->orderByDesc('start_date')
            ->limit(5)
            ->get()
            ->map(function ($sprint) {
                $totalTasks = $sprint->sprintTasks()->count();
                $completedTasks = $sprint->sprintTasks()->whereHas('task', fn ($q) => $q->where('status', 'completed'))->count();
                return [
                    'sprint_name' => $sprint->name,
                    'total_tasks' => $totalTasks,
                    'completed_tasks' => $completedTasks,
                    'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 0) : 0,
                ];
            })->toArray();

        $this->burndownData = [
            'labels' => [],
            'ideal' => [],
            'actual' => [],
        ];
    }

    public function startSprint(int $sprintId): void
    {
        Sprint::where('id', $sprintId)->update(['status' => 'active', 'start_date' => now()]);
        $this->loadData();
    }

    public function completeSprint(int $sprintId): void
    {
        Sprint::where('id', $sprintId)->update(['status' => 'completed', 'end_date' => now()]);
        $this->loadData();
    }

    public function addTaskToSprint(int $sprintId, int $taskId): void
    {
        SprintTask::firstOrCreate(['sprint_id' => $sprintId, 'task_id' => $taskId]);
        $this->loadData();
    }
}

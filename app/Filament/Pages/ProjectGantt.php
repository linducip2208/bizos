<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\Milestone;
use App\Models\Task;
use Filament\Pages\Page;

class ProjectGantt extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 905;

    protected static string $view = 'filament.pages.project-gantt';

    protected static ?string $title = 'Gantt Chart';

    protected static ?string $navigationLabel = 'Gantt Chart';

    protected static ?string $slug = 'project-gantt';

    public ?Project $project = null;
    public string $viewMode = 'Week';
    public array $tasks = [];
    public array $milestones = [];

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

        $this->tasks = Task::where('project_id', $this->project->id)
            ->whereNotNull('start_date')
            ->get()
            ->map(fn ($task) => [
                'id' => (string) $task->id,
                'name' => $task->title,
                'start' => $task->start_date?->format('Y-m-d'),
                'end' => $task->end_date?->format('Y-m-d') ?? $task->start_date?->addDays(1)->format('Y-m-d'),
                'progress' => $task->status === 'completed' ? 100 : ($task->status === 'in_progress' ? 50 : 0),
                'dependencies' => '',
            ])->toArray();

        $this->milestones = Milestone::where('project_id', $this->project->id)
            ->get()
            ->map(fn ($ms) => [
                'id' => $ms->id,
                'name' => $ms->name,
                'target_date' => $ms->target_date?->format('Y-m-d'),
                'status' => $ms->status ?? 'pending',
            ])->toArray();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }
}

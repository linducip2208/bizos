<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\SprintTask;
use App\Models\Task;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class ViewProjectSprints extends Page
{
    protected static string $resource = ProjectResource::class;

    protected string $view = 'filament.pages.project-sprints';

    protected static ?string $title = 'Sprint Management';

    protected static ?int $navigationSort = 504;

    public Project $project;
    public ?Sprint $activeSprint = null;
    public array $sprints = [];
    public array $sprintTasks = [];
    public array $allTasks = [];
    public array $burndownData = [];
    public array $velocityData = [];

    public function mount(int | string $record): void
    {
        $this->project = Project::with(['tasks', 'milestones'])->findOrFail($record);
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->sprints = Sprint::where('project_id', $this->project->id)
            ->orderBy('start_date', 'desc')
            ->get()
            ->toArray();

        $this->activeSprint = Sprint::where('project_id', $this->project->id)
            ->where('status', 'active')
            ->first();

        if ($this->activeSprint) {
            $this->sprintTasks = SprintTask::where('sprint_id', $this->activeSprint->id)
                ->with(['task.assignees'])
                ->orderBy('sort_order')
                ->get()
                ->toArray();

            $this->burndownData = $this->calculateBurndown($this->activeSprint);
            $this->velocityData = $this->calculateVelocity();
        }

        $sprintTaskIds = SprintTask::whereIn('sprint_id', Sprint::where('project_id', $this->project->id)
            ->whereIn('status', ['planning', 'active'])->pluck('id'))
            ->pluck('task_id')
            ->toArray();

        $this->allTasks = Task::where('project_id', $this->project->id)
            ->whereNotIn('id', $sprintTaskIds)
            ->where('status', '!=', 'done')
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    public function createSprint(array $data): void
    {
        $maxSort = Sprint::where('project_id', $this->project->id)->max('sort_order') ?? 0;

        Sprint::create([
            'project_id' => $this->project->id,
            'name' => $data['name'],
            'goal' => $data['goal'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'planning',
            'sort_order' => $maxSort + 1,
        ]);

        $this->loadData();

        Notification::make()
            ->title('Sprint berhasil dibuat')
            ->success()
            ->send();
    }

    public function addTaskToSprint(int $sprintId, int $taskId): void
    {
        $existing = SprintTask::where('sprint_id', $sprintId)
            ->where('task_id', $taskId)
            ->exists();

        if ($existing) {
            Notification::make()
                ->title('Tugas sudah ada di sprint ini')
                ->warning()
                ->send();
            return;
        }

        $maxSort = SprintTask::where('sprint_id', $sprintId)->max('sort_order') ?? 0;

        SprintTask::create([
            'sprint_id' => $sprintId,
            'task_id' => $taskId,
            'status' => 'todo',
            'sort_order' => $maxSort + 1,
        ]);

        $this->loadData();

        Notification::make()
            ->title('Tugas ditambahkan ke sprint')
            ->success()
            ->send();
    }

    public function removeTaskFromSprint(int $sprintTaskId): void
    {
        SprintTask::destroy($sprintTaskId);
        $this->loadData();

        Notification::make()
            ->title('Tugas dihapus dari sprint')
            ->success()
            ->send();
    }

    public function moveTaskStatus(int $sprintTaskId, string $newStatus): void
    {
        $sprintTask = SprintTask::findOrFail($sprintTaskId);
        $sprintTask->update(['status' => $newStatus]);

        $task = Task::find($sprintTask->task_id);
        if ($task) {
            $taskStatus = match ($newStatus) {
                'done' => 'done',
                'review' => 'review',
                'in_progress' => 'in_progress',
                default => 'todo',
            };
            $task->update(['status' => $taskStatus]);

            if ($newStatus === 'done' && !$task->completed_at) {
                $task->update(['completed_at' => now()]);
            }
        }

        $this->loadData();
        $this->dispatch('task-status-changed');
    }

    public function startSprint(int $sprintId): void
    {
        Sprint::where('project_id', $this->project->id)
            ->where('status', 'active')
            ->update(['status' => 'completed']);

        Sprint::where('id', $sprintId)->update(['status' => 'active']);

        $this->loadData();

        Notification::make()
            ->title('Sprint dimulai')
            ->success()
            ->send();
    }

    public function completeSprint(int $sprintId): void
    {
        $sprint = Sprint::findOrFail($sprintId);
        $sprint->update(['status' => 'completed']);

        SprintTask::where('sprint_id', $sprintId)
            ->where('status', '!=', 'done')
            ->update(['status' => 'done']);

        $this->loadData();

        Notification::make()
            ->title('Sprint selesai')
            ->success()
            ->send();
    }

    protected function calculateBurndown(Sprint $sprint): array
    {
        $startDate = Carbon::parse($sprint->start_date);
        $endDate = Carbon::parse($sprint->end_date);
        $totalDays = max($startDate->diffInDays($endDate), 1);

        $totalTasks = SprintTask::where('sprint_id', $sprint->id)->count();
        if ($totalTasks === 0) return ['labels' => [], 'ideal' => [], 'actual' => []];

        $labels = [];
        $ideal = [];
        $actual = [];

        for ($day = 0; $day <= $totalDays; $day++) {
            $currentDate = $startDate->copy()->addDays($day);
            $labels[] = $currentDate->format('d M');

            $idealRemaining = max(0, $totalTasks - ($totalTasks * ($day / $totalDays)));
            $ideal[] = round($idealRemaining, 1);

            $doneCount = SprintTask::where('sprint_id', $sprint->id)
                ->where('status', 'done')
                ->where('updated_at', '<=', $currentDate->endOfDay())
                ->count();

            $actual[] = $totalTasks - $doneCount;
        }

        return [
            'labels' => $labels,
            'ideal' => $ideal,
            'actual' => $actual,
        ];
    }

    protected function calculateVelocity(): array
    {
        $completedSprints = Sprint::where('project_id', $this->project->id)
            ->where('status', 'completed')
            ->orderBy('end_date', 'desc')
            ->limit(6)
            ->get();

        $velocityData = [];
        foreach ($completedSprints as $sprint) {
            $doneCount = SprintTask::where('sprint_id', $sprint->id)
                ->where('status', 'done')
                ->count();

            $velocityData[] = [
                'sprint_name' => $sprint->name,
                'total_tasks' => SprintTask::where('sprint_id', $sprint->id)->count(),
                'completed_tasks' => $doneCount,
                'completion_rate' => SprintTask::where('sprint_id', $sprint->id)->count() > 0
                    ? round(($doneCount / SprintTask::where('sprint_id', $sprint->id)->count()) * 100, 1)
                    : 0,
            ];
        }

        $averageVelocity = count($velocityData) > 0
            ? round(array_sum(array_column($velocityData, 'completed_tasks')) / count($velocityData), 1)
            : 0;

        return [
            'sprints' => array_reverse($velocityData),
            'average_velocity' => $averageVelocity,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_sprint')
                ->label('Buat Sprint')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    TextInput::make('name')
                        ->label('Nama Sprint')
                        ->required()
                        ->placeholder('Sprint 1, Sprint 2, dst.'),
                    Textarea::make('goal')
                        ->label('Tujuan Sprint')
                        ->rows(2),
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->required()
                        ->default(now()),
                    DatePicker::make('end_date')
                        ->label('Tanggal Selesai')
                        ->required()
                        ->default(now()->addDays(14)),
                ])
                ->action(function (array $data): void {
                    $this->createSprint($data);
                }),
        ];
    }

    protected function getViewData(): array
    {
        $activeSprint = $this->activeSprint;
        $todoTasks = [];
        $inProgressTasks = [];
        $reviewTasks = [];
        $doneTasks = [];

        foreach ($this->sprintTasks as $st) {
            switch ($st['status'] ?? 'todo') {
                case 'in_progress': $inProgressTasks[] = $st; break;
                case 'review': $reviewTasks[] = $st; break;
                case 'done': $doneTasks[] = $st; break;
                default: $todoTasks[] = $st; break;
            }
        }

        return [
            'project' => $this->project,
            'sprints' => $this->sprints,
            'activeSprint' => $activeSprint,
            'activeSprintData' => $activeSprint?->toArray(),
            'todoTasks' => $todoTasks,
            'inProgressTasks' => $inProgressTasks,
            'reviewTasks' => $reviewTasks,
            'doneTasks' => $doneTasks,
            'allTasks' => $this->allTasks,
            'burndownData' => $this->burndownData,
            'velocityData' => $this->velocityData,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Project';
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\ProgressBilling;
use App\Models\RabItem;
use App\Services\ConstructionService;
use Filament\Pages\Page;

class RabDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?int $navigationSort = 600;

    protected string $view = 'filament.pages.rab-dashboard';

    protected static ?string $title = 'Dashboard RAB';

    public static function getNavigationGroup(): ?string
    {
        return '🏗️ Konstruksi';
    }

    public array $projects = [];
    public ?int $selectedProjectId = null;
    public array $rabVsActual = [];
    public array $progressBillings = [];
    public array $projectTotals = [];

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $this->projects = Project::where('company_id', $companyId)
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->selectedProjectId = request('project_id')
            ?? Project::where('company_id', $companyId)->first()?->id;

        if ($this->selectedProjectId) {
            $this->loadData();
        }
    }

    protected function loadData(): void
    {
        $project = Project::find($this->selectedProjectId);
        if (!$project) return;

        $service = app(ConstructionService::class);
        $this->rabVsActual = $service->calculateRabVsActual($project);

        $this->progressBillings = ProgressBilling::where('project_id', $this->selectedProjectId)
            ->orderByDesc('billing_period_end')
            ->get()
            ->toArray();

        $rabItems = RabItem::where('project_id', $this->selectedProjectId)
            ->whereNull('parent_id')
            ->get();

        $this->projectTotals = [
            'rab_total' => $rabItems->sum('total_amount'),
            'billings_total' => collect($this->progressBillings)->sum('net_amount'),
            'balance' => $rabItems->sum('total_amount') - collect($this->progressBillings)->sum('net_amount'),
        ];
    }

    public function selectProject(int $projectId): void
    {
        $this->selectedProjectId = $projectId;
        $this->loadData();
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\BpmnProcess;
use BackedEnum;
use Filament\Pages\Page;

/** @deprecated Use CommandCenter with the matching tab; the legacy URL is preserved by redirect middleware. */
class ProcessMiningDashboard extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Process Mining';

    protected static ?string $navigationLabel = 'Process Mining';

    protected static ?string $slug = 'process-mining';

    protected string $view = 'filament.pages.process-mining';

    public ?int $selectedProcessId = null;

    public string $period = '30 days';

    public array $miningResults = [];

    public array $bottlenecks = [];

    public array $conformance = [];

    public array $processList = [];

    public static function getNavigationGroup(): ?string
    {
        return \App\Filament\Navigation\NavigationGroup::DASHBOARD->value;
    }

    public function mount(): void
    {
        $this->processList = BpmnProcess::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        if (!empty($this->processList)) {
            $this->selectedProcessId = array_key_first($this->processList);
            $this->loadAnalysis();
        }
    }

    public function loadAnalysis(): void
    {
        if (!$this->selectedProcessId) return;

        $bpmnService = app(\App\Services\BpmnService::class);
        $this->miningResults = $bpmnService->mineProcess($this->selectedProcessId, $this->period);
        $this->bottlenecks = $bpmnService->getBottlenecks($this->selectedProcessId);
        $this->conformance = $bpmnService->getConformance($this->selectedProcessId);
    }

    public function updatedSelectedProcessId(): void
    {
        $this->loadAnalysis();
    }

    public function updatedPeriod(): void
    {
        $this->loadAnalysis();
    }
}

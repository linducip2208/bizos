<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\KeyResult;
use App\Models\KeyResultCheckIn;
use App\Models\Objective;
use App\Models\PerformanceCycle;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Collection;

/** @deprecated Use CommandCenter with the matching tab; the legacy URL is preserved by redirect middleware. */
class OkrDashboard extends Page implements HasForms
{
    protected static bool $shouldRegisterNavigation = false;
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?int $navigationSort = 9;

    protected static ?string $title = 'Dashboard OKR';

    protected static ?string $navigationLabel = 'Dashboard OKR';

    protected static ?string $slug = 'okr-dashboard';

    protected string $view = 'filament.pages.okr-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return \App\Filament\Navigation\NavigationGroup::DASHBOARD->value;
    }

    public ?array $data = [];

    public ?PerformanceCycle $selectedCycle = null;

    public ?array $companyObjectives = [];

    public ?array $atRiskItems = [];

    public ?array $recentCheckIns = [];

    public ?array $completionStats = [];

    public ?array $cycleOptions = [];

    public ?array $departmentObjectives = [];

    public ?string $filterDepartment = null;

    public ?string $filterStatus = null;

    public function mount(): void
    {
        $this->form->fill();
        $this->cycleOptions = PerformanceCycle::orderBy('period_start', 'desc')
            ->get()
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])
            ->toArray();

        $this->loadData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('cycle_id')
                    ->label('Pilih Siklus')
                    ->options(
                        PerformanceCycle::orderBy('period_start', 'desc')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->loadData((int) $state);
                    })
                    ->nullable(),

                Select::make('filter_department')
                    ->label('Filter Departemen')
                    ->options(
                        Department::orderBy('name')->pluck('name', 'id')
                    )
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->filterDepartment = $state;
                        $this->loadData($this->data['cycle_id'] ?? null);
                    })
                    ->nullable(),

                Select::make('filter_status')
                    ->label('Filter Status')
                    ->options(Objective::statusOptions())
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->filterStatus = $state;
                        $this->loadData($this->data['cycle_id'] ?? null);
                    })
                    ->nullable(),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function loadData(?int $cycleId = null): void
    {
        $query = Objective::with(['keyResults.latestCheckIn', 'keyResults.latestCheckIn.checker', 'owner', 'cycle', 'parent', 'children'])
            ->withCount('keyResults');

        if ($cycleId) {
            $this->selectedCycle = PerformanceCycle::find($cycleId);
            $query->where('cycle_id', $cycleId);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterDepartment) {
            $query->where(function ($q) {
                $q->where(function ($inner) {
                    $inner->where('owner_type', 'App\\Models\\Department')
                        ->where('owner_id', $this->filterDepartment);
                })->orWhere(function ($inner) {
                    $inner->where('owner_type', 'App\\Models\\Employee')
                        ->whereHasMorph('owner', ['App\\Models\\Employee'], function ($q) {
                            $q->where('department_id', $this->filterDepartment);
                        });
                });
            });
        }

        $objectives = $query->orderBy('weight', 'desc')->get();

        $this->companyObjectives = $objectives
            ->where('objective_type', 'company')
            ->values()
            ->toArray();

        $this->departmentObjectives = $objectives
            ->whereIn('objective_type', ['department', 'team'])
            ->values()
            ->toArray();

        $this->atRiskItems = $objectives
            ->whereIn('status', ['at_risk', 'behind'])
            ->values()
            ->toArray();

        $totalObjectives = $objectives->count();
        $completedObjectives = $objectives->where('status', 'completed')->count();
        $onTrackObjectives = $objectives->where('status', 'on_track')->count();
        $avgProgress = $totalObjectives > 0 ? round($objectives->avg('progress_percent'), 1) : 0;

        $this->completionStats = [
            'total' => $totalObjectives,
            'completed' => $completedObjectives,
            'on_track' => $onTrackObjectives,
            'at_risk' => $objectives->where('status', 'at_risk')->count(),
            'behind' => $objectives->where('status', 'behind')->count(),
            'avg_progress' => $avgProgress,
            'completion_rate' => $totalObjectives > 0 ? round(($completedObjectives / $totalObjectives) * 100, 1) : 0,
        ];

        $this->recentCheckIns = KeyResultCheckIn::with(['keyResult.objective', 'checker'])
            ->whereHas('keyResult.objective', function ($q) use ($cycleId) {
                if ($cycleId) $q->where('cycle_id', $cycleId);
            })
            ->latest('created_at')
            ->limit(15)
            ->get()
            ->map(fn ($ci) => [
                'key_result_title' => $ci->keyResult?->title ?? '-',
                'objective_title' => $ci->keyResult?->objective?->title ?? '-',
                'value' => $ci->value,
                'unit' => $ci->keyResult?->unit ?? '',
                'notes' => $ci->notes,
                'confidence' => $ci->confidence_level,
                'on_track' => $ci->is_on_track,
                'checked_by' => $ci->checker?->name ?? '-',
                'created_at' => $ci->created_at?->diffForHumans() ?? '-',
            ])
            ->toArray();
    }
}

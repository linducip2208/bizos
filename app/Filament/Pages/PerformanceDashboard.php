<?php

namespace App\Filament\Pages;

use App\Models\PerformanceCycle;
use App\Models\PerformanceReview;
use App\Services\PerformanceService;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Collection;

class PerformanceDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 850;

    protected static ?string $title = 'Dashboard Performa';

    protected static ?string $navigationLabel = 'Dashboard Performa';

    protected static ?string $slug = 'performance-dashboard';

    protected string $view = 'filament.pages.performance-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return 'HRM';
    }

    public ?array $data = [];

    public ?PerformanceCycle $selectedCycle = null;

    public ?array $bellCurve = null;

    public ?array $recommendations = null;

    public ?array $topPerformers = [];

    public ?array $bottomPerformers = [];

    public ?array $departmentRanking = [];

    public ?array $cycleOptions = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->cycleOptions = PerformanceCycle::orderBy('period_start', 'desc')
            ->get()
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])
            ->toArray();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('cycle_id')
                    ->label('Pilih Siklus Performa')
                    ->options(
                        PerformanceCycle::orderBy('period_start', 'desc')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->loadCycleData((int) $state);
                    })
                    ->required(),
            ])
            ->statePath('data');
    }

    public function loadCycleData(int $cycleId): void
    {
        $this->selectedCycle = PerformanceCycle::find($cycleId);
        if (!$this->selectedCycle) {
            return;
        }

        $service = app(PerformanceService::class);

        $this->bellCurve = $service->getRatingDistribution($this->selectedCycle);
        $this->recommendations = $service->getRecommendations($this->selectedCycle);

        $reviews = $this->selectedCycle->reviews()
            ->where('status', 'completed')
            ->with('employee.department')
            ->get();

        $this->topPerformers = $reviews
            ->sortByDesc('final_score')
            ->take(10)
            ->map(fn ($r) => [
                'employee_name' => $r->employee?->first_name . ' ' . ($r->employee?->last_name ?? ''),
                'department' => $r->employee?->department?->name,
                'score' => $r->final_score,
                'rating' => $r->rating,
                'rating_label' => $r->rating_label,
            ])
            ->values()
            ->toArray();

        $this->bottomPerformers = $reviews
            ->sortBy('final_score')
            ->take(10)
            ->map(fn ($r) => [
                'employee_name' => $r->employee?->first_name . ' ' . ($r->employee?->last_name ?? ''),
                'department' => $r->employee?->department?->name,
                'score' => $r->final_score,
                'rating' => $r->rating,
                'rating_label' => $r->rating_label,
            ])
            ->values()
            ->toArray();

        $deptScores = [];
        foreach ($reviews as $review) {
            $dept = $review->employee?->department?->name ?? 'Tanpa Departemen';
            if (!isset($deptScores[$dept])) {
                $deptScores[$dept] = ['total' => 0, 'count' => 0];
            }
            $deptScores[$dept]['total'] += $review->final_score ?? 0;
            $deptScores[$dept]['count']++;
        }

        $this->departmentRanking = [];
        foreach ($deptScores as $dept => $data) {
            $this->departmentRanking[] = [
                'department' => $dept,
                'avg_score' => round($data['total'] / $data['count'], 2),
                'total_employees' => $data['count'],
            ];
        }

        usort($this->departmentRanking, fn ($a, $b) => $b['avg_score'] <=> $a['avg_score']);
    }

    public function getBellCurveChartData(): array
    {
        if (!$this->bellCurve) return [];
        $labels = [];
        $data = [];
        $colors = [];
        foreach ($this->bellCurve['distribution'] ?? [] as $item) {
            $labels[] = "{$item['label']} ({$item['count']})";
            $data[] = $item['count'];
            $colors[] = $item['color'];
        }
        return ['labels' => $labels, 'data' => $data, 'colors' => $colors];
    }
}

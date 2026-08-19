<?php

namespace App\Filament\Pages;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Coa;
use BackedEnum;
use Filament\Pages\Page;

class BudgetVsActual extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 401;

    protected static ?string $title = 'Budget vs Actual';

    protected static ?string $navigationLabel = 'Budget vs Actual';

    protected static ?string $slug = 'budget-vs-actual';

    protected string $view = 'filament.pages.budget-vs-actual';

    public static function getNavigationGroup(): ?string
    {
        return \App\Filament\Navigation\NavigationGroup::REPORTS->value;
    }

    public array $budgets = [];
    public ?int $selectedBudgetId = null;
    public array $budgetItems = [];
    public array $stats = [];

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $this->budgets = Budget::where('company_id', $companyId)
            ->orderByDesc('fiscal_year')
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->selectedBudgetId = request('budget_id')
            ?? Budget::where('company_id', $companyId)->latest()->first()?->id;

        if ($this->selectedBudgetId) {
            $this->loadData();
        }
    }

    public function loadData(): void
    {
        $budget = Budget::find($this->selectedBudgetId);
        if (!$budget) return;

        $items = BudgetItem::where('budget_id', $this->selectedBudgetId)
            ->with('coa')
            ->orderBy('description')
            ->get();

        $this->budgetItems = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'coa_code' => $item->coa?->code,
                'coa_name' => $item->coa?->name,
                'description' => $item->description,
                'planned_amount' => (float) $item->planned_amount,
                'actual_amount' => (float) $item->actual_amount,
                'variance' => (float) $item->variance,
                'percentage' => $item->planned_amount > 0
                    ? round(($item->actual_amount / $item->planned_amount) * 100, 1)
                    : 0,
            ];
        })->toArray();

        $totalPlanned = $items->sum('planned_amount');
        $totalActual = $items->sum('actual_amount');

        $this->stats = [
            'budget_name' => $budget->name,
            'fiscal_year' => $budget->fiscal_year,
            'total_planned' => (float) $totalPlanned,
            'total_actual' => (float) $totalActual,
            'total_variance' => (float) ($totalPlanned - $totalActual),
            'percentage' => $totalPlanned > 0
                ? round(($totalActual / $totalPlanned) * 100, 1)
                : 0,
        ];
    }

    public function selectBudget(int $budgetId): void
    {
        $this->selectedBudgetId = $budgetId;
        $this->loadData();
    }

    public function getChartLabels(): array
    {
        return array_column($this->budgetItems, 'description');
    }

    public function getChartPlanned(): array
    {
        return array_column($this->budgetItems, 'planned_amount');
    }

    public function getChartActual(): array
    {
        return array_column($this->budgetItems, 'actual_amount');
    }
}

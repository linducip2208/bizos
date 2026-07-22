<?php

namespace App\Filament\Pages;

use App\Models\Deal;
use App\Models\PipelineStage;
use Filament\Pages\Page;

class DealKanban extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-view-columns';

    protected static ?int $navigationSort = 803;

    protected static string $view = 'filament.pages.deal-kanban';

    protected static ?string $title = 'Kanban Deal';

    protected static ?string $navigationLabel = 'Kanban Deal';

    protected static ?string $slug = 'deal-kanban';

    public array $stages = [];

    public static function getNavigationGroup(): ?string
    {
        return '📈 Sales & CRM';
    }

    public function mount(): void
    {
        $this->stages = PipelineStage::with(['deals.client', 'deals.assignedTo'])
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color ?? '#6366f1',
                'deal_count' => $stage->deals->count(),
                'deals' => $stage->deals->map(fn ($deal) => [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'value' => $deal->value ?? 0,
                    'probability' => $deal->probability_percent ?? 0,
                    'client' => $deal->client ? ['name' => $deal->client->name] : null,
                    'assigned_to' => $deal->assignedTo ? [
                        'first_name' => $deal->assignedTo->first_name,
                        'last_name' => $deal->assignedTo->last_name,
                    ] : null,
                ])->toArray(),
            ])->toArray();
    }

    public function moveDeal(int $dealId, int $stageId): void
    {
        Deal::where('id', $dealId)->update(['stage_id' => $stageId]);
        $this->mount();
    }
}

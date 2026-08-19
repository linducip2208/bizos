<?php

namespace App\Filament\Pages;

use App\Services\VendorScorecardService;
use Filament\Pages\Page;

class VendorScorecard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 116;

    protected string $view = 'filament.pages.vendor-scorecard';

    protected static ?string $title = 'Scorecard Vendor';

    public static function getNavigationGroup(): ?string
    {
        return \App\Filament\Navigation\NavigationGroup::PROCUREMENT->value;
    }

    public array $scorecards = [];

    public array $summary = [];

    public array $radar = [];

    public string $gradeFilter = '';

    public string $sortBy = 'score_desc';

    public function mount(): void
    {
        $this->gradeFilter = (string) request('grade', '');
        $this->sortBy = (string) request('sort', 'score_desc');

        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(VendorScorecardService::class);
        $scorecards = $service->getAllScorecards();

        if ($this->gradeFilter !== '') {
            $scorecards = $scorecards->filter(fn (array $card) => $card['grade'] === $this->gradeFilter)->values();
        }

        $scorecards = (match ($this->sortBy) {
            'score_asc' => $scorecards->sortBy('overall_score'),
            'total_orders_desc' => $scorecards->sortByDesc('total_orders'),
            'total_value_desc' => $scorecards->sortByDesc('total_value'),
            'name_asc' => $scorecards->sortBy('supplier_name'),
            default => $scorecards->sortByDesc('overall_score'),
        })->values();

        $this->scorecards = $scorecards->toArray();
        $this->summary = $this->buildSummary($scorecards);
        $this->radar = $this->buildRadar($scorecards);
    }

    protected function buildSummary($scorecards): array
    {
        $total = $scorecards->count();

        $withData = $scorecards->filter(fn (array $card) => $card['has_data']);

        $avgScore = $withData->isEmpty()
            ? 0.0
            : $withData->avg('overall_score');

        $top = $withData->sortByDesc('overall_score')->first();
        $worst = $withData->sortBy('overall_score')->first();

        return [
            'total_suppliers' => $total,
            'avg_score' => round((float) $avgScore, 1),
            'top_performer' => $top,
            'worst_performer' => $worst,
        ];
    }

    protected function buildRadar($scorecards): array
    {
        $top = collect($scorecards)->firstWhere('has_data', true);

        if (!$top) {
            return [];
        }

        return [
            'labels' => ['Pengiriman Tepat Waktu', 'Kualitas', 'Harga', 'Respons'],
            'values' => [
                (float) $top['on_time_delivery'],
                (float) $top['quality_acceptance'],
                (float) $top['price_competitiveness'],
                (float) $top['response_time'],
            ],
            'supplier_name' => $top['supplier_name'],
        ];
    }
}

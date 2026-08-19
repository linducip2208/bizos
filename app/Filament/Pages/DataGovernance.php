<?php

namespace App\Filament\Pages;

use App\Services\MasterDataService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DataGovernance extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 446;

    protected static ?string $title = 'Tata Kelola Data';

    protected static ?string $slug = 'data-governance';

    protected string $view = 'filament.pages.data-governance';

    public array $qualityScores = [];
    public array $duplicateReport = [];
    public string $activeEntityType = '';
    public array $activeDuplicates = [];
    public ?int $mergeTargetId = null;
    public array $mergeSourceIds = [];
    public array $mergePreview = [];
    public bool $showMergeModal = false;

    public static function getNavigationGroup(): ?string
    {
        return 'System';
    }

    public function mount(MasterDataService $service): void
    {
        $this->qualityScores = $service->getDataQualityScore();
        $this->duplicateReport = $service->getDuplicateReport();
    }

    public function detectDuplicates(string $entityType, MasterDataService $service): void
    {
        $this->activeEntityType = $entityType;
        $report = $service->getDuplicateReport();
        $this->duplicateReport = $report;

        if (isset($report[$entityType])) {
            $this->activeDuplicates = $report[$entityType]['details'] ?? [];
        }

        Notification::make()
            ->title('Deteksi duplikat selesai')
            ->body("Ditemukan " . count($this->activeDuplicates) . " grup duplikat untuk " . ($report[$entityType]['label'] ?? $entityType))
            ->success()
            ->send();
    }

    public function previewMerge(int $targetId, array $sourceIds, string $entityType, MasterDataService $service): void
    {
        $this->mergeTargetId = $targetId;
        $this->mergeSourceIds = $sourceIds;
        $this->activeEntityType = $entityType;
        $this->showMergeModal = true;

        $config = $service->entityConfig[$entityType] ?? null;
        if (!$config) {
            return;
        }

        $model = $config['model'];
        $target = $model::find($targetId);
        $sources = $model::whereIn('id', $sourceIds)->get();

        $this->mergePreview = [
            'entity_type' => $entityType,
            'entity_label' => $config['label'],
            'target' => $target?->toArray() ?? [],
            'sources' => $sources->map(fn($s) => $s->toArray())->toArray(),
            'target_name' => $target?->{$config['nameField']} ?? $target?->name ?? 'Unknown',
            'source_names' => $sources->map(fn($s) => $s->{$config['nameField']} ?? $s->name ?? 'Unknown')->toArray(),
        ];
    }

    public function executeMerge(MasterDataService $service): void
    {
        if (empty($this->mergeTargetId) || empty($this->mergeSourceIds) || empty($this->activeEntityType)) {
            Notification::make()
                ->title('Data tidak lengkap')
                ->danger()
                ->send();
            return;
        }

        try {
            $target = $service->mergeEntities($this->activeEntityType, $this->mergeTargetId, $this->mergeSourceIds);

            $this->qualityScores = $service->getDataQualityScore();
            $this->duplicateReport = $service->getDuplicateReport();

            if (isset($this->duplicateReport[$this->activeEntityType])) {
                $this->activeDuplicates = $this->duplicateReport[$this->activeEntityType]['details'] ?? [];
            }

            $this->showMergeModal = false;
            $this->mergeTargetId = null;
            $this->mergeSourceIds = [];
            $this->mergePreview = [];

            Notification::make()
                ->title('Penggabungan berhasil')
                ->body('Data berhasil digabungkan. Log penggabungan telah dicatat.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal menggabungkan data')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function closeMergeModal(): void
    {
        $this->showMergeModal = false;
        $this->mergeTargetId = null;
        $this->mergeSourceIds = [];
        $this->mergePreview = [];
    }

    public function refreshAll(MasterDataService $service): void
    {
        $this->qualityScores = $service->getDataQualityScore();
        $this->duplicateReport = $service->getDuplicateReport();
        $this->activeDuplicates = [];

        Notification::make()
            ->title('Data diperbarui')
            ->success()
            ->send();
    }

    protected function getQualityColor(float $score): string
    {
        if ($score >= 80) return 'emerald';
        if ($score >= 60) return 'amber';
        return 'red';
    }

    public function getQualityColorPublic(float $score): string
    {
        return $this->getQualityColor($score);
    }
}

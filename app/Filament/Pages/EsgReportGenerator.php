<?php

namespace App\Filament\Pages;

use App\Services\EsgService;
use Filament\Pages\Page;

class EsgReportGenerator extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.esg-report-generator';

    protected static ?string $title = 'Generator Laporan ESG';

    public string $selectedPeriod = '';
    public string $selectedFramework = 'gri';
    public ?string $generatedPath = null;
    public string $message = '';
    public bool $success = false;

    public static function getNavigationGroup(): ?string
    {
        return 'ESG';
    }

    public function mount(): void
    {
        $this->selectedPeriod = now()->format('Y-m');
    }

    public function generateReport(): void
    {
        $user = auth()->user();
        if (!$user || !$user->company_id) {
            $this->message = 'Tidak dapat mengidentifikasi perusahaan.';
            $this->success = false;
            return;
        }

        try {
            $esgService = app(EsgService::class);
            $path = $esgService->generateEsgReport($user->company_id, $this->selectedPeriod, $this->selectedFramework);

            $this->generatedPath = $path;
            $this->message = 'Laporan ESG berhasil digenerate!';
            $this->success = true;
        } catch (\Exception $e) {
            $this->message = 'Gagal generate laporan: ' . $e->getMessage();
            $this->success = false;
        }
    }
}

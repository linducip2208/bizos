<?php

namespace App\Filament\Pages;

use App\Models\AppFile;
use App\Services\DocumentClassifierService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class DocumentClassifierDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?int $navigationSort = 1305;

    protected static ?string $title = 'Klasifikasi Dokumen';

    protected static ?string $navigationLabel = 'Klasifikasi Dokumen';

    protected static ?string $slug = 'document-classifier';

    protected static string $view = 'filament.pages.document-classifier';

    public static function getNavigationGroup(): ?string
    {
        return 'AI Analytics';
    }

    public ?array $data = [];
    public ?array $classificationResult = [];
    public ?array $batchResult = [];
    public ?array $extractedData = [];
    public bool $isProcessing = false;
    public string $activeTab = 'classify';
    public ?string $uploadedPath = null;
    public int $companyId;

    public function mount(): void
    {
        $this->companyId = auth()->user()->company_id;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('document')
                    ->label('Upload Dokumen')
                    ->disk('public')
                    ->directory('tmp/classifier')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'text/plain'])
                    ->maxSize(10240)
                    ->required()
                    ->live(),
            ])
            ->statePath('data');
    }

    public function classifyUploaded(): void
    {
        $this->isProcessing = true;
        $this->classificationResult = [];
        $this->extractedData = [];

        try {
            $uploadedFile = $this->data['document'] ?? null;
            if (!$uploadedFile) {
                $this->classificationResult = ['error' => 'Silakan upload dokumen terlebih dahulu.'];
                $this->isProcessing = false;
                return;
            }

            $filePath = Storage::disk('public')->path($uploadedFile);
            if (!file_exists($filePath)) {
                $this->classificationResult = ['error' => 'File tidak ditemukan. Silakan upload ulang.'];
                $this->isProcessing = false;
                return;
            }

            $service = app(DocumentClassifierService::class);
            $this->classificationResult = $service->classify($filePath);

            if (!empty($this->classificationResult['document_type']) && $this->classificationResult['document_type'] !== 'other') {
                $this->extractedData = $service->extractData($filePath, $this->classificationResult['document_type']);
            }
        } catch (\Exception $e) {
            $this->classificationResult = ['error' => 'Gagal klasifikasi: ' . $e->getMessage()];
        }

        $this->isProcessing = false;
    }

    public function runBatchClassify(): void
    {
        $this->isProcessing = true;
        $service = app(DocumentClassifierService::class);
        $this->batchResult = $service->batchClassify($this->companyId);
        $this->isProcessing = false;
        $this->activeTab = 'batch';
    }

    public function autoFileAll(): void
    {
        $this->isProcessing = true;
        $service = app(DocumentClassifierService::class);

        $files = AppFile::whereNull('folder_id')->limit(50)->get();
        $count = 0;
        foreach ($files as $file) {
            try {
                $service->autoFile($file);
                $count++;
            } catch (\Exception $e) {
            }
        }

        $this->batchResult = [
            'classified' => $count,
            'total' => count($files),
            'message' => "Berhasil auto-file {$count} dari " . count($files) . " dokumen.",
        ];
        $this->isProcessing = false;
        $this->activeTab = 'batch';
    }

    public function getConfidenceColor(int $confidence): string
    {
        if ($confidence >= 80) return '#22c55e';
        if ($confidence >= 60) return '#f59e0b';
        return '#ef4444';
    }

    public function getConfidenceLabel(int $confidence): string
    {
        if ($confidence >= 80) return 'Yakin';
        if ($confidence >= 60) return 'Cukup';
        return 'Rendah';
    }
}

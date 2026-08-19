<?php

namespace App\Filament\Pages;

use App\Services\DataImportService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class DataImportWizard extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?int $navigationSort = 448;

    protected static ?string $title = 'Import Data';

    protected static ?string $slug = 'data-import-wizard';

    protected string $view = 'filament.pages.data-import-wizard';

    public static function getNavigationGroup(): ?string
    {
        return 'System';
    }

    public int $step = 1;

    public string $entity = '';

    public $csvFile = null;

    public ?string $filePath = null;

    public array $headers = [];

    public int $totalRows = 0;

    public array $preview = [];

    public array $mapping = [];

    public array $validation = [];

    public array $importResult = [];

    protected DataImportService $importService;

    public function mount(): void
    {
        $this->importService = app(DataImportService::class);
    }

    public function getImportableEntitiesProperty(): array
    {
        return $this->importService->getImportableEntities();
    }

    public function getEntitiesProperty(): array
    {
        return $this->importService->getEntities();
    }

    public function getEntityFieldsProperty(): array
    {
        if (!$this->entity) {
            return [];
        }
        return $this->importService->getEntityFields($this->entity);
    }

    public function getImportLogsProperty(): array
    {
        return $this->importService->getImportLogs()->toArray();
    }

    public function selectEntity(string $entity): void
    {
        $this->entity = $entity;
        $this->resetWizardState();
        $this->step = 2;
    }

    public function uploadCsv(): void
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            $this->filePath = $this->csvFile->store('imports', 'public');

            $parsed = $this->importService->parseCsv($this->filePath);

            if (empty($parsed['headers'])) {
                throw new \RuntimeException('File CSV tidak memiliki baris header.');
            }

            $this->headers = $parsed['headers'];
            $this->totalRows = $parsed['total_rows'];
            $this->preview = $parsed['preview'];
            $this->autoSuggestMapping();

            $this->step = 3;

            Notification::make()
                ->title('File CSV berhasil diupload')
                ->body($parsed['total_rows'] . ' baris data terdeteksi · ' . count($parsed['headers']) . ' kolom')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal upload CSV')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function autoSuggestMapping(): void
    {
        $fields = $this->importService->getEntityFields($this->entity);
        $allFields = collect(array_merge($fields['required'], $fields['optional']))->keyBy('name');

        $mapping = [];
        $used = [];

        foreach ($this->headers as $index => $header) {
            $normalized = $this->normalizeKey($header);
            $match = null;

            foreach ($allFields as $field => $def) {
                if ($this->normalizeKey($field) === $normalized && !in_array($field, $used, true)) {
                    $match = $field;
                    break;
                }
            }

            if (!$match) {
                foreach ($allFields as $field => $def) {
                    if (in_array($field, $used, true)) {
                        continue;
                    }
                    $label = $this->normalizeKey($def['label']);
                    if ($label === $normalized || str_contains($normalized, $this->normalizeKey($field)) || str_contains($this->normalizeKey($field), $normalized)) {
                        $match = $field;
                        break;
                    }
                }
            }

            if ($match) {
                $used[] = $match;
            }
            $mapping[$index] = $match ?? '';
        }

        $this->mapping = $mapping;
    }

    public function validateAndPreview(): void
    {
        try {
            $serviceMapping = $this->serviceMapping();

            $requiredFields = collect($this->importService->getEntityFields($this->entity)['required'])->pluck('name')->toArray();
            foreach ($requiredFields as $field) {
                if (!isset($serviceMapping[$field])) {
                    Notification::make()
                        ->title('Pemetaan belum lengkap')
                        ->body('Kolom wajib "' . $field . '" belum dipetakan.')
                        ->warning()
                        ->send();
                    return;
                }
            }

            $parsed = $this->importService->parseCsv($this->filePath);
            $this->validation = $this->importService->validateRows($parsed['rows'], $this->entity, $serviceMapping);

            $this->step = 4;

            if (!$this->validation['has_errors']) {
                Notification::make()
                    ->title('Semua baris valid')
                    ->body($this->validation['valid_rows'] . ' baris siap diimport.')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Ditemukan ' . $this->validation['error_count'] . ' kesalahan')
                    ->body($this->validation['error_rows'] . ' baris bermasalah. Baris yang valid tetap bisa diimport.')
                    ->warning()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal validasi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function confirmImport(): void
    {
        try {
            $serviceMapping = $this->serviceMapping();
            $parsed = $this->importService->parseCsv($this->filePath);

            $this->importService->setFilename($this->csvFile?->getClientOriginalName() ?? basename($this->filePath));

            $result = $this->importService->import($this->entity, $parsed['rows'], $serviceMapping, auth()->id());

            $this->importResult = $result;
            $this->step = 5;

            Notification::make()
                ->title('Import selesai')
                ->body("{$result['success_count']} berhasil · {$result['error_count']} gagal")
                ->color($result['success_count'] > 0 ? 'success' : 'danger')
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Import gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetWizard(): void
    {
        $this->resetWizardState();
        $this->step = 1;
    }

    public function downloadTemplate(string $entity): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $service = app(DataImportService::class);
        $config = $service->entityConfig($entity);
        $csv = $service->generateTemplate($entity);

        return response()->streamDownload(
            fn () => print $csv,
            'template-import-' . $entity . '.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function processImport(): \Illuminate\Http\JsonResponse
    {
        try {
            $data = request()->validate([
                'entity' => 'required|string',
                'mapping' => 'required|array',
                'rows' => 'required|array',
            ]);

            $service = app(DataImportService::class);
            $service->setFilename((string) request()->input('filename'));

            $result = $service->import($data['entity'], $data['rows'], $data['mapping'], auth()->id());

            return response()->json(['success' => true, 'result' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    protected function resetWizardState(): void
    {
        $this->csvFile = null;
        $this->filePath = null;
        $this->headers = [];
        $this->totalRows = 0;
        $this->preview = [];
        $this->mapping = [];
        $this->validation = [];
        $this->importResult = [];
    }

    protected function serviceMapping(): array
    {
        $mapping = [];
        foreach ($this->mapping as $index => $entityField) {
            $csvHeader = $this->headers[$index] ?? null;
            if ($csvHeader === null || $entityField === null || $entityField === '') {
                continue;
            }
            $mapping[$entityField] = $csvHeader;
        }
        return $mapping;
    }

    protected function normalizeKey(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '', $value);
        return $value;
    }
}

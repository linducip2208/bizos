<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use App\Services\ReceiptOcrService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ExpenseScanner extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-camera';

    protected static ?int $navigationSort = 113;

    protected static ?string $title = 'Pindai Pengeluaran';

    protected static ?string $slug = 'expense-scanner';

    protected string $view = 'filament.pages.expense-scanner';

    public ?string $state = 'upload';
    public $receiptImage = null;
    public ?string $receiptPath = null;
    public ?string $receiptPreview = null;
    public ?array $ocrResult = null;
    public ?string $ocrError = null;
    public bool $ocrBusy = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->state = 'upload';
        $this->form->fill();
    }

    public static function getNavigationGroup(): ?string
    {
        return '👥 Human Capital';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->label('Karyawan')
                    ->options(
                        Employee::query()
                            ->orderBy('first_name')
                            ->get()
                            ->mapWithKeys(fn ($e) => [$e->id => trim($e->first_name . ' ' . $e->last_name)])
                    )
                    ->searchable()
                    ->required()
                    ->live(),

                Select::make('category_id')
                    ->label('Kategori')
                    ->options(
                        ReimbursementCategory::where('is_active', true)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->required()
                    ->live(),

                DatePicker::make('date')
                    ->label('Tanggal Transaksi')
                    ->native(false)
                    ->displayFormat('d M Y')
                    ->required()
                    ->live(),

                TextInput::make('amount')
                    ->label('Jumlah (Rp)')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->live(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->nullable()
                    ->live(),
            ])
            ->statePath('data');
    }

    public function updatedReceiptImage(): void
    {
        if (!$this->receiptImage) {
            return;
        }

        $this->state = 'processing';
        $this->ocrBusy = true;
        $this->ocrResult = null;
        $this->ocrError = null;

        try {
            $path = $this->receiptImage->store('receipts', 'public');
            $this->receiptPath = $path;
            $this->receiptPreview = Storage::disk('public')->url($path);

            $ocrService = app(ReceiptOcrService::class);
            $result = $ocrService->processReceipt($path);

            if (!empty($result['error'])) {
                $this->ocrError = $result['error'];
                $this->state = 'error';
            } else {
                $this->ocrResult = $result;
                $this->state = 'result';
                $this->fillFormFromOcr($result);
            }
        } catch (\Throwable $e) {
            $this->ocrError = $e->getMessage();
            $this->state = 'error';
        } finally {
            $this->ocrBusy = false;
        }
    }

    public function submitReimbursement(): void
    {
        $data = $this->form->getState();

        $reimbursement = Reimbursement::create([
            'employee_id' => $data['employee_id'],
            'category_id' => $data['category_id'],
            'date' => $data['date'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'status' => 'draft',
            'receipt_image_path' => $this->receiptPath,
            'ocr_data' => $this->ocrResult,
            'ocr_confidence' => $this->ocrResult['confidence'] ?? null,
            'ocr_status' => 'completed',
        ]);

        $this->state = 'submitted';

        Notification::make()
            ->title('Reimbursement berhasil diajukan')
            ->body('Pengajuan "' . ($data['description'] ?? 'Tanpa deskripsi') . '" telah disimpan.')
            ->success()
            ->send();

        $this->reset([
            'receiptImage', 'receiptPath', 'receiptPreview', 'ocrResult', 'ocrError',
        ]);
        $this->form->fill();
    }

    public function rescan(): void
    {
        $this->reset([
            'receiptImage', 'receiptPath', 'receiptPreview', 'ocrResult', 'ocrError',
            'ocrBusy',
        ]);
        $this->form->fill();
        $this->state = 'upload';
    }

    public function useOcrData(): void
    {
        if ($this->ocrResult) {
            $this->fillFormFromOcr($this->ocrResult);
            $this->state = 'edit';
        }
    }

    public function backToUpload(): void
    {
        $this->state = 'upload';
    }

    protected function fillFormFromOcr(array $result): void
    {
        $this->form->fill([
            'date' => $result['transaction_date'] ?? null,
            'amount' => $result['total_amount'] ?? null,
            'description' => $this->buildDescription($result),
            'category_id' => $result['category_id'] ?? null,
        ]);
    }

    protected function buildDescription(array $ocrData): string
    {
        $merchant = $ocrData['merchant_name'] ?? $ocrData['vendor_name'] ?? '';
        $receipt = $ocrData['receipt_number'] ?? '';
        $items = collect($ocrData['line_items'] ?? [])->pluck('description')->take(3)->implode(', ');

        $parts = array_filter([$merchant, $receipt, $items]);
        return implode(' - ', $parts) ?: '';
    }
}

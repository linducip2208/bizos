<?php

namespace App\Http\Livewire;

use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use App\Services\OcrReceiptService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ReceiptScanner extends Component
{
    use WithFileUploads;

    public $receiptImage;
    public $ocrResult = [];
    public $isProcessing = false;
    public $isProcessed = false;
    public $showEditMode = false;
    public $error = null;
    public $duplicateDetected = false;
    public $budgetInfo = null;
    public $successMessage = null;

    public $vendorName = '';
    public $transactionDate = '';
    public $totalAmount = 0;
    public $taxAmount = 0;
    public $lineItems = [];
    public $paymentMethod = '';
    public $receiptNumber = '';

    public $categoryId = null;
    public $employeeId = null;
    public $departmentId = null;
    public $description = '';

    protected $rules = [
        'receiptImage' => 'required|image|max:10240',
        'vendorName' => 'nullable|string|max:255',
        'transactionDate' => 'nullable|date',
        'totalAmount' => 'required|numeric|min:0',
        'taxAmount' => 'nullable|numeric|min:0',
        'paymentMethod' => 'nullable|string|max:100',
        'receiptNumber' => 'nullable|string|max:100',
        'categoryId' => 'nullable|exists:reimbursement_categories,id',
        'description' => 'nullable|string|max:1000',
    ];

    protected $listeners = ['resetScanner' => 'resetForm'];

    public function mount($employeeId = null, $departmentId = null)
    {
        $this->employeeId = $employeeId ?? auth()->user()?->employee_id;
        $this->departmentId = $departmentId;
    }

    public function updatedReceiptImage()
    {
        $this->validateOnly('receiptImage');
        $this->processImage();
    }

    public function processImage()
    {
        $this->isProcessing = true;
        $this->isProcessed = false;
        $this->error = null;
        $this->duplicateDetected = false;
        $this->budgetInfo = null;
        $this->successMessage = null;

        try {
            $path = $this->receiptImage->store('receipts', 'public');

            $ocrService = app(OcrReceiptService::class);
            $result = $ocrService->processReceipt($path);

            $this->ocrResult = $result;
            $this->populateFields($result);

            $this->duplicateDetected = $ocrService->detectDuplicate($result, $this->employeeId);

            if ($this->categoryId && $this->departmentId) {
                $this->budgetInfo = $ocrService->checkBudget(
                    $this->categoryId,
                    $result['total_amount'] ?? 0,
                    $this->departmentId
                );
            }

            $this->isProcessed = true;
        } catch (\Exception $e) {
            $this->error = 'Gagal memproses struk: ' . $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    public function createReimbursement()
    {
        $this->validate([
            'totalAmount' => 'required|numeric|min:1',
            'transactionDate' => 'nullable|date',
        ]);

        try {
            $ocrResult = [
                'vendor_name' => $this->vendorName,
                'transaction_date' => $this->transactionDate,
                'total_amount' => $this->totalAmount,
                'tax_amount' => $this->taxAmount,
                'line_items' => $this->lineItems,
                'payment_method' => $this->paymentMethod,
                'receipt_number' => $this->receiptNumber,
            ];

            $ocrService = app(OcrReceiptService::class);
            $reimbursement = $ocrService->createReimbursementDraft($ocrResult, $this->employeeId);

            if ($this->categoryId) {
                $reimbursement->update(['category_id' => $this->categoryId]);
            }

            if ($this->description) {
                $reimbursement->update(['description' => $this->description]);
            }

            if ($this->receiptImage) {
                $existingPath = str_replace('receipts/', '', $this->ocrResult['_path'] ?? '');
                if ($existingPath && Storage::disk('public')->exists('receipts/' . $existingPath)) {
                    $reimbursement->reimbursementAttachments()->create([
                        'file_name' => 'struk_' . time() . '.jpg',
                        'file_path' => 'receipts/' . $existingPath,
                        'file_type' => 'image/jpeg',
                    ]);
                }
            }

            $this->successMessage = 'Reimbursement berhasil dibuat! ID: #' . $reimbursement->id;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->error = 'Gagal membuat reimbursement: ' . $e->getMessage();
        }
    }

    public function resetForm()
    {
        $this->receiptImage = null;
        $this->ocrResult = [];
        $this->isProcessed = false;
        $this->isProcessing = false;
        $this->showEditMode = false;
        $this->error = null;
        $this->duplicateDetected = false;
        $this->budgetInfo = null;
        $this->successMessage = null;

        $this->vendorName = '';
        $this->transactionDate = '';
        $this->totalAmount = 0;
        $this->taxAmount = 0;
        $this->lineItems = [];
        $this->paymentMethod = '';
        $this->receiptNumber = '';
        $this->categoryId = null;
        $this->description = '';
    }

    public function render()
    {
        $categories = ReimbursementCategory::where('is_active', true)->orderBy('name')->get();

        return view('livewire.receipt-scanner', [
            'categories' => $categories,
        ]);
    }

    protected function populateFields(array $result): void
    {
        $this->vendorName = $result['vendor_name'] ?? '';
        $this->transactionDate = $result['transaction_date'] ?? now()->toDateString();
        $this->totalAmount = $result['total_amount'] ?? 0;
        $this->taxAmount = $result['tax_amount'] ?? 0;
        $this->lineItems = $result['line_items'] ?? [];
        $this->paymentMethod = $result['payment_method'] ?? '';
        $this->receiptNumber = $result['receipt_number'] ?? '';
        $this->description = $result['vendor_name'] ?? '';

        if (isset($result['vendor_name']) && !$this->categoryId) {
            $ocrService = app(OcrReceiptService::class);
            $category = $ocrService->categorize($result);
            if ($category) {
                $this->categoryId = $category->id;
            }
        }
    }
}

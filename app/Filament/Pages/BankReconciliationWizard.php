<?php

namespace App\Filament\Pages;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Services\BankReconciliationService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class BankReconciliationWizard extends Page implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?int $navigationSort = 335;

    protected static ?string $title = 'Rekonsiliasi Bank';

    protected static ?string $slug = 'bank-reconciliation-wizard';

    protected string $view = 'filament.pages.bank-reconciliation-wizard';

    public static function getNavigationGroup(): ?string
    {
        return '💵 Finance & Accounting';
    }

    public ?array $data = [];

    public int $step = 1;

    public ?int $bankAccountId = null;

    public ?BankAccount $bankAccount = null;

    public $statementFile = null;

    public ?string $uploadedFilePath = null;

    public array $statementData = [];

    public array $matchResult = [];

    public array $manualMatches = [];

    public ?int $selectedBtxId = null;

    public string $periodStart = '';

    public string $periodEnd = '';

    public float $openingBalance = 0;

    public float $closingBalance = 0;

    public float $statementBalance = 0;

    public string $notes = '';

    public ?int $createdReconciliationId = null;

    public function mount(): void
    {
        $this->periodStart = now()->startOfMonth()->toDateString();
        $this->periodEnd = now()->toDateString();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('bank_account_id')
                    ->label('Rekening Bank')
                    ->options(fn () => BankAccount::where('is_active', true)
                        ->orderBy('bank_name')
                        ->pluck('account_name', 'id')
                        ->toArray())
                    ->searchable()
                    ->required()
                    ->live(),
            ])
            ->statePath('data');
    }

    public function selectAccount(): void
    {
        $this->validate(['bankAccountId' => 'required|integer|exists:bank_accounts,id']);

        $this->bankAccount = BankAccount::find($this->bankAccountId);

        $this->step = 2;
    }

    public function uploadStatement(): void
    {
        $this->validate([
            'statementFile' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            $path = $this->statementFile->store('bank-statements', 'public');

            $service = app(BankReconciliationService::class);

            $this->bankAccount = BankAccount::find($this->bankAccountId);
            $result = $service->uploadStatement($this->bankAccount, $path);

            $this->uploadedFilePath = $path;
            $this->statementData = $result;

            $this->runAutoMatch();

            $this->step = 3;

            Notification::make()
                ->title('Statement berhasil diupload')
                ->body("Format terdeteksi: {$result['format']} · {$result['total_rows']} transaksi ditemukan")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal upload statement')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function runAutoMatch(): void
    {
        $service = app(BankReconciliationService::class);
        $this->matchResult = $service->autoMatch($this->statementData['rows'], $this->bankAccountId);
    }

    public function manualMatch(int $unmatchedIndex): void
    {
        if (!$this->selectedBtxId) {
            Notification::make()
                ->title('Pilih transaksi bank terlebih dahulu')
                ->warning()
                ->send();
            return;
        }

        $unmatchedItem = $this->matchResult['unmatched'][$unmatchedIndex] ?? null;
        if (!$unmatchedItem) return;

        $tx = BankTransaction::find($this->selectedBtxId);
        if (!$tx) return;

        $stmt = $unmatchedItem['statement'];

        $this->matchResult['matched'][] = [
            'statement' => $stmt,
            'transaction' => $tx->toArray(),
            'score' => 100,
            'manual' => true,
        ];

        array_splice($this->matchResult['unmatched'], $unmatchedIndex, 1);

        $this->selectedBtxId = null;

        $this->matchResult['matched_count'] = count($this->matchResult['matched']);
        $this->matchResult['unmatched_count'] = count($this->matchResult['unmatched']);

        Notification::make()
            ->title('Transaksi berhasil dicocokkan manual')
            ->success()
            ->send();
    }

    public function skipUnmatched(int $unmatchedIndex): void
    {
        $item = $this->matchResult['unmatched'][$unmatchedIndex] ?? null;
        if ($item) {
            $item['skipped'] = true;
            $this->matchResult['unmatched'][$unmatchedIndex] = $item;
        }
    }

    public function unskipUnmatched(int $unmatchedIndex): void
    {
        $item = $this->matchResult['unmatched'][$unmatchedIndex] ?? null;
        if ($item) {
            $item['skipped'] = false;
            $this->matchResult['unmatched'][$unmatchedIndex] = $item;
        }
    }

    public function createReconciliation(): void
    {
        try {
            $service = app(BankReconciliationService::class);

            $this->bankAccount = BankAccount::find($this->bankAccountId);

            $meta = [
                'company_id' => auth()->user()->company_id,
                'period_start' => $this->periodStart,
                'period_end' => $this->periodEnd,
                'opening_balance' => $this->openingBalance ?: $this->bankAccount->current_balance,
                'closing_balance' => $this->closingBalance ?: $this->bankAccount->current_balance,
                'statement_balance' => $this->statementBalance ?: $this->getStatementEndingBalance(),
                'notes' => $this->notes,
                'statement_file_path' => $this->uploadedFilePath,
            ];

            $reconciliation = $service->createReconciliation(
                $this->bankAccount,
                $this->matchResult['matched'] ?? [],
                $this->matchResult['unmatched'] ?? [],
                $meta
            );

            $this->createdReconciliationId = $reconciliation->id;
            $this->step = 5;

            Notification::make()
                ->title('Rekonsiliasi berhasil dibuat')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal membuat rekonsiliasi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getStatementEndingBalance(): float
    {
        $rows = $this->statementData['rows'] ?? [];
        if (empty($rows)) return 0;

        $last = end($rows);
        return (float) ($last['balance'] ?? 0);
    }

    public function getMatchedTotal(): float
    {
        $total = 0;
        foreach (($this->matchResult['matched'] ?? []) as $m) {
            $total += abs((float) ($m['statement']['amount'] ?? 0));
        }
        return round($total, 2);
    }

    public function getUnmatchedTotal(): float
    {
        $total = 0;
        foreach (($this->matchResult['unmatched'] ?? []) as $m) {
            $total += abs((float) ($m['statement']['amount'] ?? 0));
        }
        return round($total, 2);
    }

    public function getVariance(): float
    {
        return round($this->closingBalance - $this->getMatchedTotal(), 2);
    }

    public function getBankAccountsProperty(): array
    {
        return BankAccount::where('is_active', true)
            ->orderBy('bank_name')
            ->pluck('account_name', 'id')
            ->toArray();
    }

    public function getAvailableBankTransactionsProperty(): array
    {
        if (!$this->bankAccountId) return [];

        return BankTransaction::where('bank_account_id', $this->bankAccountId)
            ->where('is_reconciled', false)
            ->orderBy('transaction_date', 'desc')
            ->take(500)
            ->get()
            ->mapWithKeys(fn ($tx) => [
                $tx->id => '#' . $tx->id . ' | ' . $tx->transaction_date?->format('d/m/Y') . ' | ' . number_format($tx->amount, 0, ',', '.') . ' | ' . $tx->description,
            ])
            ->toArray();
    }
}

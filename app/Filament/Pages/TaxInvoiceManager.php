<?php

namespace App\Filament\Pages;

use App\Services\EFakturService;
use App\Services\PphCalculatorService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TaxInvoiceManager extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 805;

    protected static ?string $title = 'Manajer Faktur Pajak';

    protected static ?string $navigationLabel = 'e-Faktur';

    protected static ?string $slug = 'tax-invoice-manager';

    protected string $view = 'filament.pages.tax-invoice-manager';

    public static function getNavigationGroup(): ?string
    {
        return 'Alat Hitung';
    }

    public ?array $generateFormData = [];
    public ?array $npwpValidationResult = null;
    public ?array $npwpFormData = [];
    public ?array $pph23Result = null;
    public ?array $pph4Result = null;
    public ?array $pph26Result = null;
    public ?array $pphFormData = [];
    public ?string $generatedInvoice = null;
    public string $activeTab = 'generate';

    public function mount(): void
    {
        $this->generateForm->fill([
            'kode_transaksi' => '01',
            'kode_status' => '0',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    protected function getForms(): array
    {
        return [
            'generateForm',
            'npwpForm',
            'pphForm',
        ];
    }

    // ── Generate Nomor Faktur ──

    public function generateForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('kode_transaksi')
                    ->label('Kode Transaksi')
                    ->options(EFakturService::getKodeTransaksi())
                    ->required()
                    ->default('01'),

                Select::make('kode_status')
                    ->label('Kode Status')
                    ->options(EFakturService::getKodeStatus())
                    ->required()
                    ->default('0'),

                TextInput::make('prefix')
                    ->label('Prefix (opsional)')
                    ->default('000')
                    ->maxLength(3),
            ])
            ->statePath('generateFormData');
    }

    public function generateInvoiceNumber(): void
    {
        $data = $this->generateForm->getState();
        $service = new EFakturService();

        $this->generatedInvoice = $service->generateTaxInvoiceNumber(
            $data['kode_transaksi'] ?? '01',
            $data['kode_status'] ?? '0',
            $data['prefix'] ?? '000'
        );

        Notification::make()
            ->title('Nomor faktur berhasil digenerate: ' . $this->generatedInvoice)
            ->success()
            ->send();
    }

    // ── Validasi NPWP ──

    public function npwpForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('npwp')
                    ->label('Nomor NPWP (15 digit)')
                    ->placeholder('00.000.000.0-000.000')
                    ->required()
                    ->maxLength(25),
            ])
            ->statePath('npwpFormData');
    }

    public function validateNpwp(): void
    {
        $data = $this->npwpForm->getState();
        $npwp = $data['npwp'] ?? '';

        if (empty($npwp)) {
            Notification::make()->title('Masukkan NPWP terlebih dahulu')->danger()->send();
            return;
        }

        $service = new EFakturService();
        $this->npwpValidationResult = $service->validateNpwp($npwp);

        $status = $this->npwpValidationResult['valid'] ? 'NPWP Valid' : 'NPWP Tidak Valid';

        Notification::make()
            ->title($status)
            ->status($this->npwpValidationResult['valid'] ? 'success' : 'danger')
            ->send();
    }

    // ── Kalkulasi PPh ──

    public function pphForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('pph_type')
                    ->label('Jenis PPh')
                    ->options([
                        'pph23'  => 'PPh 23 (Jasa, Sewa, Royalti, dll.)',
                        'pph4'   => 'PPh 4(2) Final (Sewa Bangunan, Konstruksi, dll.)',
                        'pph26'  => 'PPh 26 (WP Luar Negeri)',
                    ])
                    ->required()
                    ->default('pph23')
                    ->reactive(),

                Select::make('transaction_type')
                    ->label('Tipe Transaksi')
                    ->options(function (callable $get) {
                        $pphType = $get('pph_type');
                        if ($pphType === 'pph23') return PphCalculatorService::getTransactionTypes();
                        if ($pphType === 'pph4') return PphCalculatorService::getPph4ayat2Types();
                        return [];
                    })
                    ->required()
                    ->visible(fn(callable $get) => in_array($get('pph_type'), ['pph23', 'pph4'])),

                TextInput::make('dpp')
                    ->label('DPP (Dasar Pengenaan Pajak — Rp)')
                    ->numeric()
                    ->required()
                    ->prefix('Rp'),

                TextInput::make('npwp')
                    ->label('NPWP (opsional — untuk PPh 23)')
                    ->placeholder('Kosongkan jika tidak ada NPWP → tarif 2×')
                    ->visible(fn(callable $get) => $get('pph_type') === 'pph23'),

                Select::make('tax_treaty_country')
                    ->label('Negara Tax Treaty (P3B — opsional)')
                    ->options([
                        'SG' => 'Singapura',
                        'MY' => 'Malaysia',
                        'JP' => 'Jepang',
                        'KR' => 'Korea Selatan',
                        'CN' => 'China',
                        'AU' => 'Australia',
                        'GB' => 'Inggris',
                        'US' => 'Amerika Serikat',
                        'DE' => 'Jerman',
                        'FR' => 'Perancis',
                        'NL' => 'Belanda',
                        'HK' => 'Hong Kong',
                        'AE' => 'Uni Emirat Arab',
                    ])
                    ->visible(fn(callable $get) => $get('pph_type') === 'pph26'),
            ])
            ->statePath('pphFormData');
    }

    public function calculatePph(): void
    {
        $data = $this->pphForm->getState();
        $pphType = $data['pph_type'] ?? 'pph23';
        $dpp = (float) ($data['dpp'] ?? 0);
        $transactionType = $data['transaction_type'] ?? 'jasa';

        if ($dpp <= 0) {
            Notification::make()->title('DPP harus > 0')->danger()->send();
            return;
        }

        $service = new PphCalculatorService();

        $this->pph23Result = null;
        $this->pph4Result = null;
        $this->pph26Result = null;

        if ($pphType === 'pph23') {
            $npwp = $data['npwp'] ?? null;
            $this->pph23Result = $service->calculatePph23($transactionType, $dpp, $npwp);
        } elseif ($pphType === 'pph4') {
            $this->pph4Result = $service->calculatePph4ayat2($transactionType, $dpp);
        } elseif ($pphType === 'pph26') {
            $treaty = $data['tax_treaty_country'] ?? null;
            $this->pph26Result = $service->calculatePph26($dpp, $treaty);
        }

        Notification::make()->title('Perhitungan PPh selesai')->success()->send();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->npwpValidationResult = null;
        $this->generatedInvoice = null;
        $this->pph23Result = null;
        $this->pph4Result = null;
        $this->pph26Result = null;
    }
}

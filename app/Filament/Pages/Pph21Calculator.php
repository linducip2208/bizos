<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Services\Pph21TerService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Pph21Calculator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?int $navigationSort = 801;

    protected static ?string $title = 'Kalkulator PPh 21 TER';

    protected static ?string $navigationLabel = 'PPh 21 TER';

    protected static ?string $slug = 'pph21-calculator';

    protected string $view = 'filament.pages.pph21-calculator';

    public static function getNavigationGroup(): ?string
    {
        return 'Alat Hitung';
    }

    public ?array $data = [];

    public ?array $result = null;

    public ?array $reconciliationResult = null;

    public ?array $grossUpResult = null;

    public ?array $employeeList = [];

    public string $activeTab = 'monthly';

    public function mount(): void
    {
        $this->form->fill([
            'gross_monthly_salary' => 5000000,
            'ptkp_code' => 'TK/0',
        ]);

        $this->employeeList = Employee::where('status', 'active')
            ->select('id', 'first_name', 'last_name', 'basic_salary', 'ptkp_code')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'label' => trim($e->first_name . ' ' . ($e->last_name ?? '')) . ' — Rp ' . number_format($e->basic_salary),
                'salary' => (float) $e->basic_salary,
                'ptkp_code' => $e->ptkp_code ?? 'TK/0',
            ])
            ->toArray();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->label('Pilih Karyawan (Opsional)')
                    ->options(fn() => collect($this->employeeList)->pluck('label', 'id')->toArray())
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $emp = collect($this->employeeList)->firstWhere('id', (int) $state);
                        if ($emp) {
                            $this->form->fill([
                                'gross_monthly_salary' => $emp['salary'],
                                'ptkp_code' => $emp['ptkp_code'],
                            ]);
                        }
                    }),

                TextInput::make('gross_monthly_salary')
                    ->label('Gaji Bruto Bulanan (Rp)')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->default(5000000),

                Select::make('ptkp_code')
                    ->label('Status PTKP')
                    ->options([
                        'TK/0' => 'TK/0 — Tidak Kawin, 0 Tanggungan (Rp 54.000.000)',
                        'TK/1' => 'TK/1 — Tidak Kawin, 1 Tanggungan (Rp 58.500.000)',
                        'TK/2' => 'TK/2 — Tidak Kawin, 2 Tanggungan (Rp 63.000.000)',
                        'TK/3' => 'TK/3 — Tidak Kawin, 3 Tanggungan (Rp 67.500.000)',
                        'K/0'  => 'K/0  — Kawin, 0 Tanggungan (Rp 58.500.000)',
                        'K/1'  => 'K/1  — Kawin, 1 Tanggungan (Rp 63.000.000)',
                        'K/2'  => 'K/2  — Kawin, 2 Tanggungan (Rp 67.500.000)',
                        'K/3'  => 'K/3  — Kawin, 3 Tanggungan (Rp 72.000.000)',
                    ])
                    ->required()
                    ->default('TK/0'),

                TextInput::make('yearly_salary_for_recon')
                    ->label('Total Gaji Setahun (untuk Rekonsiliasi)')
                    ->numeric()
                    ->prefix('Rp')
                    ->visible(fn() => $this->activeTab === 'yearly'),

                TextInput::make('pph21_paid_for_recon')
                    ->label('Total PPh 21 TER Sudah Dibayar Setahun')
                    ->numeric()
                    ->prefix('Rp')
                    ->visible(fn() => $this->activeTab === 'yearly'),

                TextInput::make('desired_take_home')
                    ->label('Take Home Pay yang Diinginkan')
                    ->numeric()
                    ->prefix('Rp')
                    ->visible(fn() => $this->activeTab === 'grossup'),
            ])
            ->statePath('data');
    }

    public function calculate(): void
    {
        $service = new Pph21TerService();
        $data = $this->form->getState();

        $salary = (float) ($data['gross_monthly_salary'] ?? 0);
        $ptkpCode = $data['ptkp_code'] ?? 'TK/0';

        if ($salary <= 0) {
            Notification::make()
                ->title('Gaji harus lebih dari 0')
                ->danger()
                ->send();
            return;
        }

        $this->result = $service->calculateMonthlyTer($salary, $ptkpCode);

        Notification::make()
            ->title('Perhitungan selesai')
            ->success()
            ->send();
    }

    public function calculateReconciliation(): void
    {
        $service = new Pph21TerService();
        $data = $this->form->getState();

        $yearlySalary = (float) ($data['yearly_salary_for_recon'] ?? 0);
        $paid = (float) ($data['pph21_paid_for_recon'] ?? 0);
        $ptkpCode = $data['ptkp_code'] ?? 'TK/0';

        if ($yearlySalary <= 0) {
            Notification::make()->title('Gaji tahunan harus diisi')->danger()->send();
            return;
        }

        $this->reconciliationResult = $service->calculateYearlyReconciliation($paid, $yearlySalary, $ptkpCode);

        Notification::make()->title('Rekonsiliasi tahunan selesai')->success()->send();
    }

    public function calculateGrossUp(): void
    {
        $service = new Pph21TerService();
        $data = $this->form->getState();

        $desiredTakeHome = (float) ($data['desired_take_home'] ?? 0);
        $ptkpCode = $data['ptkp_code'] ?? 'TK/0';

        if ($desiredTakeHome <= 0) {
            Notification::make()->title('Take home pay harus diisi')->danger()->send();
            return;
        }

        $this->grossUpResult = $service->calculateGrossUp($desiredTakeHome, $ptkpCode);

        Notification::make()->title('Perhitungan gross-up selesai')->success()->send();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->result = null;
        $this->reconciliationResult = null;
        $this->grossUpResult = null;
    }

    public function getTerBrackets(string $category = 'A'): array
    {
        return Pph21TerService::getBrackets($category);
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Services\ThrCalculatorService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class ThrCalculator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static ?int $navigationSort = 803;

    protected static ?string $title = 'Kalkulator THR';

    protected static ?string $navigationLabel = 'THR';

    protected static ?string $slug = 'thr-calculator';

    protected string $view = 'filament.pages.thr-calculator';

    public static function getNavigationGroup(): ?string
    {
        return 'Alat Hitung';
    }

    public ?array $data = [];

    public ?array $result = null;

    public ?array $batchResult = null;

    public string $activeTab = 'single';

    public function mount(): void
    {
        $this->form->fill([
            'reference_date' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->label('Pilih Karyawan')
                    ->options(fn() => Employee::where('status', 'active')
                        ->get()
                        ->mapWithKeys(fn($e) => [
                            $e->id => trim($e->first_name . ' ' . ($e->last_name ?? '')) . ' (' . $e->employee_code . ')'
                        ])
                        ->toArray()
                    )
                    ->searchable()
                    ->required(fn() => $this->activeTab === 'single'),

                DatePicker::make('reference_date')
                    ->label('Tanggal Acuan (default: H-7 Idul Fitri)')
                    ->native(false)
                    ->displayFormat('d M Y'),
            ])
            ->statePath('data');
    }

    public function calculateSingle(): void
    {
        $data = $this->form->getState();
        $employeeId = $data['employee_id'] ?? null;

        if (!$employeeId) {
            Notification::make()->title('Pilih karyawan terlebih dahulu')->danger()->send();
            return;
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            Notification::make()->title('Karyawan tidak ditemukan')->danger()->send();
            return;
        }

        $refDate = !empty($data['reference_date'])
            ? Carbon::parse($data['reference_date'])
            : null;

        $service = new ThrCalculatorService();
        $this->result = $service->calculateThr($employee, $refDate);

        $this->batchResult = null;

        Notification::make()->title('THR dihitung')->success()->send();
    }

    public function calculateBatch(): void
    {
        $data = $this->form->getState();

        $refDate = !empty($data['reference_date'])
            ? Carbon::parse($data['reference_date'])
            : null;

        $employees = Employee::where('status', 'active')->get();

        $service = new ThrCalculatorService();
        $this->batchResult = $service->calculateBatchThr($employees, $refDate);

        $this->result = null;

        Notification::make()
            ->title('THR batch dihitung untuk ' . count($employees) . ' karyawan')
            ->success()
            ->send();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->result = null;
        $this->batchResult = null;
    }
}

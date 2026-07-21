<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Services\OvertimeCalculatorService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class OvertimeSimulator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 804;

    protected static ?string $title = 'Simulator Lembur';

    protected static ?string $navigationLabel = 'Lembur';

    protected static ?string $slug = 'overtime-simulator';

    protected string $view = 'filament.pages.overtime-simulator';

    public static function getNavigationGroup(): ?string
    {
        return 'Alat Hitung';
    }

    public ?array $data = [];

    public ?array $result = null;

    public function mount(): void
    {
        $this->form->fill([
            'day_type' => 'workday',
            'hours_worked' => 2,
            'has_break' => true,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->label('Pilih Karyawan (Opsional, isi manual jika kosong)')
                    ->options(fn() => Employee::where('status', 'active')
                        ->get()
                        ->mapWithKeys(fn($e) => [
                            $e->id => trim($e->first_name . ' ' . ($e->last_name ?? '')) . ' — Rp ' . number_format($e->basic_salary)
                        ])
                        ->toArray()
                    )
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        if ($state) {
                            $emp = Employee::find($state);
                            if ($emp) {
                                $this->form->fill([
                                    'monthly_salary' => (float) $emp->basic_salary,
                                ]);
                            }
                        }
                    }),

                TextInput::make('monthly_salary')
                    ->label('Gaji Bulanan (Rp)')
                    ->numeric()
                    ->required()
                    ->prefix('Rp'),

                TextInput::make('hours_worked')
                    ->label('Total Jam Kerja (termasuk istirahat)')
                    ->numeric()
                    ->required()
                    ->step(0.5)
                    ->default(2),

                Select::make('day_type')
                    ->label('Tipe Hari')
                    ->options(OvertimeCalculatorService::getDayTypes())
                    ->required()
                    ->default('workday'),

                Select::make('has_break')
                    ->label('Ada Jam Istirahat?')
                    ->options([
                        true  => 'Ya (jika ≥ 4 jam, potong 1 jam)',
                        false => 'Tidak (tanpa potongan istirahat)',
                    ])
                    ->required()
                    ->default(true),
            ])
            ->statePath('data');
    }

    public function calculate(): void
    {
        $data = $this->form->getState();

        $monthlySalary = (float) ($data['monthly_salary'] ?? 0);
        $hoursWorked = (float) ($data['hours_worked'] ?? 0);
        $dayType = $data['day_type'] ?? 'workday';
        $hasBreak = (bool) ($data['has_break'] ?? true);

        if ($monthlySalary <= 0 || $hoursWorked <= 0) {
            Notification::make()->title('Gaji dan jam kerja harus > 0')->danger()->send();
            return;
        }

        $employee = new Employee(['basic_salary' => $monthlySalary]);

        $service = new OvertimeCalculatorService();
        $this->result = $service->calculateOvertime($employee, $hoursWorked, $dayType, $hasBreak);

        Notification::make()->title('Simulasi lembur selesai')->success()->send();
    }
}

<?php

namespace App\Filament\Pages;

use App\Services\BpjsCalculatorService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BpjsSimulator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    protected static ?int $navigationSort = 802;

    protected static ?string $title = 'Simulator BPJS';

    protected static ?string $navigationLabel = 'BPJS Simulator';

    protected static ?string $slug = 'bpjs-simulator';

    protected string $view = 'filament.pages.bpjs-simulator';

    public static function getNavigationGroup(): ?string
    {
        return 'Alat Hitung';
    }

    public ?array $data = [];

    public ?array $result = null;

    public function mount(): void
    {
        $this->form->fill([
            'salary' => 5000000,
            'risk_grade' => 'medium',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('salary')
                    ->label('Gaji Bulanan (Rp)')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->default(5000000),

                Select::make('risk_grade')
                    ->label('Tingkat Risiko Kerja (JKK)')
                    ->options(BpjsCalculatorService::getRiskGrades())
                    ->required()
                    ->default('medium'),
            ])
            ->statePath('data');
    }

    public function calculate(): void
    {
        $service = new BpjsCalculatorService();
        $data = $this->form->getState();

        $salary = (float) ($data['salary'] ?? 0);
        $riskGrade = $data['risk_grade'] ?? 'medium';

        if ($salary <= 0) {
            Notification::make()->title('Gaji harus lebih dari 0')->danger()->send();
            return;
        }

        $this->result = $service->calculateAllContributions($salary, $riskGrade);

        Notification::make()->title('Simulasi BPJS selesai')->success()->send();
    }
}

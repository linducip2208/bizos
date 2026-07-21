<?php

namespace App\Filament\Resources\PayrollSimulation\Pages;

use App\Filament\Resources\PayrollSimulation\PayrollSimulationResource;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;

class ViewPayrollSimulation extends ViewRecord
{
    protected static string $resource = PayrollSimulationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runSimulation')
                ->label('Jalankan Simulasi')
                ->icon('heroicon-o-play')
                ->action(function () {
                    $simulation = $this->getRecord();
                    $service = app(\App\Services\PayrollService::class);
                    $employeeIds = $simulation->config['employee_ids'] ?? [];
                    $changes = $simulation->config['changes'] ?? [];
                    $result = $service->simulatePayroll($employeeIds, $changes);
                    $simulation->update(['result' => $result]);
                    $this->fillForm();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Simulasi'),
                        TextEntry::make('company.name')
                            ->label('Perusahaan'),
                        TextEntry::make('creator.first_name')
                            ->label('Dibuat Oleh'),
                    ])
                    ->columns(3),
                Section::make('Hasil Simulasi')
                    ->schema([
                        TextEntry::make('result.total_employees')
                            ->label('Jumlah Karyawan')
                            ->visible(fn ($record) => !empty($record->result)),
                        TextEntry::make('result.total_gross')
                            ->label('Total Bruto')
                            ->money('IDR')
                            ->visible(fn ($record) => !empty($record->result)),
                        TextEntry::make('result.total_deductions')
                            ->label('Total Potongan')
                            ->money('IDR')
                            ->visible(fn ($record) => !empty($record->result)),
                        TextEntry::make('result.total_net')
                            ->label('Total Bersih')
                            ->money('IDR')
                            ->visible(fn ($record) => !empty($record->result)),
                    ])
                    ->columns(4)
                    ->visible(fn ($record) => !empty($record->result)),
            ]);
    }
}

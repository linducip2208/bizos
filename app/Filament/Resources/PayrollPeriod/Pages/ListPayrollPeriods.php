<?php

namespace App\Filament\Resources\PayrollPeriod\Pages;

use App\Filament\Resources\PayrollPeriod\PayrollPeriodResource;
use App\Models\PayrollPeriod;
use App\Services\PayrollIntegrationService;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPayrollPeriods extends ListRecords
{
    protected static string $resource = PayrollPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('generateFromAttendance')
                ->label('Generate dari Absensi')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generate Payroll dari Data Absensi')
                ->modalDescription('Pilih periode gaji yang ingin di-generate dari data absensi karyawan.')
                ->modalSubmitActionLabel('Generate')
                ->form([
                    \Filament\Forms\Components\Select::make('period_id')
                        ->label('Periode Gaji')
                        ->options(
                            PayrollPeriod::where('status', 'draft')
                                ->orderBy('start_date', 'desc')
                                ->get()
                                ->mapWithKeys(fn ($p) => [
                                    $p->id => $p->period_code . ' (' . $p->start_date->format('d M Y') . ' - ' . $p->end_date->format('d M Y') . ')',
                                ])
                        )
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $period = PayrollPeriod::findOrFail($data['period_id']);
                    $service = app(PayrollIntegrationService::class);

                    try {
                        $result = $service->generateAndSaveFromAttendance($period);

                        Notification::make()
                            ->title('Payroll berhasil di-generate')
                            ->body("Total {$result['total_employees']} karyawan diproses. Total net: Rp " . number_format($result['total_net'], 0, ',', '.'))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal generate payroll')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
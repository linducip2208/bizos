<?php

namespace App\Filament\Resources\PayrollPeriod\Pages;

use App\Filament\Resources\PayrollPeriod\PayrollPeriodResource;
use App\Services\FinancialIntegrationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPayrollPeriod extends EditRecord
{
    protected static string $resource = PayrollPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('postingJurnal')
                ->label('Posting Jurnal')
                ->icon('heroicon-o-document-check')
                ->color('success')
                ->visible(fn() => $this->record->status === 'completed')
                ->requiresConfirmation()
                ->modalHeading('Posting Jurnal Payroll')
                ->modalDescription(fn() => "Posting jurnal untuk periode {$this->record->period_code}? Total Gross: Rp " . number_format($this->record->total_gross, 2, ',', '.'))
                ->action(function () {
                    try {
                        $service = app(FinancialIntegrationService::class);
                        $journal = $service->postPayrollToJournal($this->record);

                        Notification::make()
                            ->title('Jurnal berhasil diposting')
                            ->body("Jurnal {$journal->journal_number} telah dibuat.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal posting jurnal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            DeleteAction::make(),
        ];
    }
}
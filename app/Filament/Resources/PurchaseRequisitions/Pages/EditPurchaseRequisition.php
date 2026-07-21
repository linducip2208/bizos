<?php

namespace App\Filament\Resources\PurchaseRequisitions\Pages;

use App\Filament\Resources\PurchaseRequisitions\PurchaseRequisitionResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPurchaseRequisition extends EditRecord
{
    protected static string $resource = PurchaseRequisitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submitForApproval')
                ->label('Ajukan Persetujuan')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->visible(fn ($record) => $record->getApprovalStatus() === 'draft')
                ->action(function ($record) {
                    try {
                        $record->submitForApproval();
                        Notification::make()
                            ->title('Berhasil diajukan untuk persetujuan')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal mengajukan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->isPendingApproval())
                ->requiresConfirmation()
                ->modalHeading('Approve Permintaan Pembelian')
                ->modalDescription('Anda yakin ingin menyetujui permintaan pembelian ini?')
                ->modalSubmitActionLabel('Ya, Setujui')
                ->action(function ($record) {
                    try {
                        $employeeId = Auth::user()?->employee_id;
                        $record->approve($employeeId);
                        Notification::make()
                            ->title('Permintaan pembelian disetujui')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal approve: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => $record->isPendingApproval())
                ->requiresConfirmation()
                ->modalHeading('Reject Permintaan Pembelian')
                ->modalDescription('Anda yakin ingin menolak permintaan pembelian ini?')
                ->modalSubmitActionLabel('Ya, Tolak')
                ->action(function ($record) {
                    try {
                        $employeeId = Auth::user()?->employee_id;
                        $record->reject($employeeId, 'Ditolak');
                        Notification::make()
                            ->title('Permintaan pembelian ditolak')
                            ->danger()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal reject: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            DeleteAction::make(),
        ];
    }
}

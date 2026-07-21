<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Services\FinancialIntegrationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

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
                ->modalHeading('Approve Pesanan Pembelian')
                ->modalDescription('Anda yakin ingin menyetujui pesanan pembelian ini?')
                ->modalSubmitActionLabel('Ya, Setujui')
                ->action(function ($record) {
                    try {
                        $employeeId = Auth::user()?->employee_id;
                        $record->approve($employeeId);
                        Notification::make()
                            ->title('Pesanan pembelian disetujui')
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
                ->modalHeading('Reject Pesanan Pembelian')
                ->modalDescription('Anda yakin ingin menolak pesanan pembelian ini?')
                ->modalSubmitActionLabel('Ya, Tolak')
                ->action(function ($record) {
                    try {
                        $employeeId = Auth::user()?->employee_id;
                        $record->reject($employeeId, 'Ditolak');
                        Notification::make()
                            ->title('Pesanan pembelian ditolak')
                            ->danger()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal reject: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('validasiTigaArah')
                ->label('Validasi 3-Arah')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning')
                ->visible(fn ($record) => in_array($record->status, ['partially_received', 'received']))
                ->action(function ($record) {
                    try {
                        $service = app(FinancialIntegrationService::class);
                        $result = $service->validateThreeWayMatch($record);

                        if ($result['matched']) {
                            Notification::make()
                                ->title('Validasi 3-Arah: Cocok')
                                ->body("PO, GRN, dan Invoice sudah sesuai. PO: {$result['po_number']}")
                                ->success()
                                ->send();
                        } else {
                            $discrepancyList = implode("\n", array_map(fn($d) => "• {$d['message']}", $result['discrepancies']));
                            Notification::make()
                                ->title('Validasi 3-Arah: Ada Selisih')
                                ->body("Ditemukan {$result['discrepancy_count']} ketidakcocokan:\n{$discrepancyList}")
                                ->warning()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal validasi')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            DeleteAction::make(),
        ];
    }
}

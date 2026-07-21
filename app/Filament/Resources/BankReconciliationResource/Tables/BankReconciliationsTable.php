<?php

namespace App\Filament\Resources\BankReconciliationResource\Tables;

use App\Services\BankReconciliationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BankReconciliationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('bankAccount.bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('period_start')
                    ->label('Periode Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('period_end')
                    ->label('Periode Akhir')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('statement_balance')
                    ->label('Saldo Rek. Koran')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('closing_balance')
                    ->label('Saldo Sistem')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('difference')
                    ->label('Selisih')
                    ->money('IDR')
                    ->color(fn ($state) => $state != 0 ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draft',
                        'in_progress' => 'Dalam Proses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('period_end', 'desc')
            ->recordActions([
                Action::make('auto_match')
                    ->label('Auto Match')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('info')
                    ->action(function ($record) {
                        $service = app(BankReconciliationService::class);
                        $result = $service->autoMatch($record);
                        $record->update(['status' => 'in_progress']);
                        Notification::make()
                            ->title('Auto Match Selesai')
                            ->body("{$result['matched']} cocok, {$result['unmatched_journal']} jurnal tidak cocok, {$result['unmatched_bank']} bank tidak cocok")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'in_progress'])),
                Action::make('complete')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($record) {
                        $service = app(BankReconciliationService::class);
                        $service->calculateDifference($record);
                        $service->completeReconciliation($record);
                        Notification::make()
                            ->title('Rekonsiliasi Selesai')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'in_progress'])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

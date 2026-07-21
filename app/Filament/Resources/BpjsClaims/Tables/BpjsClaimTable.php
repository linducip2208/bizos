<?php

namespace App\Filament\Resources\BpjsClaims\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Services\HealthcareService;
use Filament\Notifications\Notification;

class BpjsClaimTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('claim_number')
                    ->label('No. Klaim')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('patient.full_name')
                    ->label('Pasien')
                    ->searchable(),
                TextColumn::make('sep_number')
                    ->label('No. SEP')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('ina_cbgs_code')
                    ->label('INA-CBGs')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('claim_amount')
                    ->label('Jumlah Klaim')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('approved_amount')
                    ->label('Disetujui')
                    ->money('IDR')
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'submitted' => 'Terkirim',
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'paid' => 'Dibayar',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'info',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'paid' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('submitted_at')
                    ->label('Tgl Kirim')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('submit')
                    ->label('Submit Klaim')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(function ($record) {
                        $claim = app(HealthcareService::class)->submitBpjsClaim($record->medicalRecord);
                        Notification::make()
                            ->title('Klaim berhasil disubmit')
                            ->body("No. Klaim: {$claim->claim_number}")
                            ->success()
                            ->send();
                    }),
                Action::make('check_status')
                    ->label('Cek Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => in_array($record->status, ['submitted', 'pending']))
                    ->action(function ($record) {
                        $status = app(HealthcareService::class)->checkBpjsClaimStatus($record);
                        Notification::make()
                            ->title('Status Klaim: ' . match ($status) {
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'pending' => 'Pending',
                                'submitted' => 'Terkirim',
                                default => $status,
                            })
                            ->color($status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning'))
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\ProgressBillings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProgressBillingTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('billing_number')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.name')
                    ->label('Proyek')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('billing_period_start')
                    ->label('Periode Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('billing_period_end')
                    ->label('Periode Selesai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('physical_progress_percent')
                    ->label('Progres Fisik')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('current_claimed_percent')
                    ->label('Klaim Saat Ini')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('gross_amount')
                    ->label('Nilai Kotor')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('retention_amount')
                    ->label('Retensi')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('net_amount')
                    ->label('Nilai Bersih')
                    ->money('IDR')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'submitted',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'primary' => 'paid',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'submitted' => 'Diajukan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'paid' => 'Dibayar',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
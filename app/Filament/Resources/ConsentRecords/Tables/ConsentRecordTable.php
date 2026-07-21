<?php

namespace App\Filament\Resources\ConsentRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class ConsentRecordTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('person_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'employee' => 'Karyawan',
                        'client' => 'Klien',
                        'supplier' => 'Supplier',
                        default => $state,
                    }),
                TextColumn::make('person_id')
                    ->label('ID Subjek')
                    ->sortable(),
                TextColumn::make('purpose')
                    ->label('Tujuan')
                    ->badge()
                    ->searchable(),
                TextColumn::make('method')
                    ->label('Metode')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'active' => 'success',
                        'withdrawn' => 'danger',
                        'expired' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('consented_at')
                    ->label('Disetujui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Kedaluwarsa')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
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

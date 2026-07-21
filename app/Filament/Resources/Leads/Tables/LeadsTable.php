<?php

namespace App\Filament\Resources\Leads\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('Nama Depan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Nama Belakang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),
                TextColumn::make('company_name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('source.name')
                    ->label('Sumber')
                    ->sortable(),
                TextColumn::make('assignedTo.first_name')
                    ->label('Ditugaskan')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baru' => 'gray',
                        'dihubungi' => 'warning',
                        'terkualifikasi' => 'success',
                        'tidak_tertarik' => 'danger',
                        'terkonversi' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'baru' => 'Baru',
                        'dihubungi' => 'Dihubungi',
                        'terkualifikasi' => 'Terkualifikasi',
                        'tidak_tertarik' => 'Tidak Tertarik',
                        'terkonversi' => 'Terkonversi',
                        default => $state,
                    }),
                TextColumn::make('score')
                    ->label('Skor')
                    ->sortable(),
                TextColumn::make('next_follow_up')
                    ->label('Follow Up')
                    ->date('d M Y H:i')
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

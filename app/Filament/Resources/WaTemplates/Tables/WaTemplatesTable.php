<?php

namespace App\Filament\Resources\WaTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WaTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'marketing' => 'info',
                        'transaksional' => 'success',
                        'layanan' => 'warning',
                        'pengingat' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'marketing' => 'Marketing',
                        'transaksional' => 'Transaksional',
                        'layanan' => 'Layanan',
                        'pengingat' => 'Pengingat',
                        default => $state,
                    }),
                TextColumn::make('language')
                    ->label('Bahasa')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'id' => 'Indonesia',
                        'en' => 'Inggris',
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'aktif' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'aktif' => 'Aktif',
                        'ditolak' => 'Ditolak',
                        default => $state,
                    }),
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

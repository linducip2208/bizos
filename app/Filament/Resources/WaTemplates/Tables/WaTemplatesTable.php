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
                        'marketing', 'MARKETING' => 'info',
                        'transaksional', 'TRANSACTIONAL' => 'success',
                        'layanan', 'SERVICE' => 'warning',
                        'pengingat', 'REMINDER' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match (strtolower($state)) {
                        'marketing' => 'Marketing',
                        'transactional' => 'Transaksional',
                        'transaksional' => 'Transaksional',
                        'service' => 'Layanan',
                        'layanan' => 'Layanan',
                        'reminder' => 'Pengingat',
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
                TextColumn::make('meta_template_status')
                    ->label('Status Meta')
                    ->badge()
                    ->color(fn ($record) => $record->status_color)
                    ->formatStateUsing(fn ($record) => $record->status_label)
                    ->placeholder('Draft'),
                TextColumn::make('meta_template_id')
                    ->label('Meta ID')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('quality_score')
                    ->label('Skor')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'GREEN' => 'success',
                        'YELLOW' => 'warning',
                        'RED' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('meta_rejection_reason')
                    ->label('Alasan Ditolak')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('danger'),
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

<?php

namespace App\Filament\Resources\WaAutoReplies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WaAutoRepliesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('keyword')
                    ->label('Kata Kunci')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('match_type')
                    ->label('Tipe Pencocokan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'exact' => 'info',
                        'contains' => 'warning',
                        'starts_with' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'exact' => 'Persis',
                        'contains' => 'Mengandung',
                        'starts_with' => 'Dimulai Dengan',
                        default => $state,
                    }),
                TextColumn::make('reply_text')
                    ->label('Teks Balasan')
                    ->limit(50)
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
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
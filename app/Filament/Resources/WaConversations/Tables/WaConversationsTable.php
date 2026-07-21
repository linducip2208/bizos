<?php

namespace App\Filament\Resources\WaConversations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WaConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contact_name')
                    ->label('Nama Kontak')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_phone')
                    ->label('No. Telepon')
                    ->searchable(),
                TextColumn::make('last_message')
                    ->label('Pesan Terakhir')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('unread_count')
                    ->label('Belum Dibaca')
                    ->sortable(),
                TextColumn::make('assignedTo.first_name')
                    ->label('Ditugaskan')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'tertunda' => 'warning',
                        'selesai' => 'gray',
                        'spam' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aktif' => 'Aktif',
                        'tertunda' => 'Tertunda',
                        'selesai' => 'Selesai',
                        'spam' => 'Spam',
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
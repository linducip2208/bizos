<?php

namespace App\Filament\Resources\TicketResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('No. Tiket')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('client.name')
                    ->label('Klien')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('assignedTo.first_name')
                    ->label('Ditugaskan')
                    ->sortable()
                    ->placeholder('Belum'),
                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'urgent' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'urgent' => 'Urgent',
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'info',
                        'in_progress' => 'warning',
                        'waiting_on_customer' => 'gray',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Terbuka',
                        'in_progress' => 'Dalam Proses',
                        'waiting_on_customer' => 'Menunggu Pelanggan',
                        'resolved' => 'Terselesaikan',
                        'closed' => 'Tertutup',
                        default => $state,
                    }),
                TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'portal' => 'Portal',
                        'email' => 'Email',
                        'phone' => 'Telepon',
                        'chat' => 'Chat',
                        'internal' => 'Internal',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y H:i')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Batas Waktu')
                    ->date('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),
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

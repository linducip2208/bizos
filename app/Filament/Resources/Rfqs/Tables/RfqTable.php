<?php

namespace App\Filament\Resources\Rfqs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RfqTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rfq_number')
                    ->label('Nomor RFQ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'open' => 'Terbuka',
                        'closed' => 'Tertutup',
                        'awarded' => 'Diberikan',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'open' => 'success',
                        'closed' => 'warning',
                        'awarded' => 'primary',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('submission_deadline')
                    ->label('Batas Pengumpulan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('rfq_suppliers_count')
                    ->label('Supplier')
                    ->counts('rfqSuppliers')
                    ->sortable(),
                TextColumn::make('bids_count')
                    ->label('Penawaran')
                    ->counts('bids')
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'open' => 'Terbuka',
                        'closed' => 'Tertutup',
                        'awarded' => 'Diberikan',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}

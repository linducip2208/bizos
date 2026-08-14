<?php

namespace App\Filament\Resources\Bids\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BidTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bid_number')
                    ->label('Nomor Penawaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rfq.rfq_number')
                    ->label('RFQ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'submitted' => 'Terkirim',
                        'shortlisted' => 'Terpilih',
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'info',
                        'shortlisted' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('delivery_lead_time_days')
                    ->label('Estimasi (hari)')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('evaluation_score')
                    ->label('Skor')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('submitted_at')
                    ->label('Terkirim')
                    ->dateTime('d M Y H:i')
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
                        'submitted' => 'Terkirim',
                        'shortlisted' => 'Terpilih',
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                    ]),
                SelectFilter::make('rfq_id')
                    ->label('RFQ')
                    ->relationship('rfq', 'rfq_number'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}

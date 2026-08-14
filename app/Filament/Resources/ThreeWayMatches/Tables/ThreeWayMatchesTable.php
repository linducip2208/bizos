<?php

namespace App\Filament\Resources\ThreeWayMatches\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ThreeWayMatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchaseOrder.po_number')
                    ->label('Nomor PO')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('goodsReceipt.grn_number')
                    ->label('Nomor Penerimaan')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('invoice.invoice_number')
                    ->label('Nomor Faktur')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('match_status')
                    ->label('Status Pencocokan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'matched' => 'Cocok',
                        'partial_match' => 'Cocok Sebagian',
                        'mismatch' => 'Tidak Cocok',
                        'pending' => 'Menunggu',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'matched' => 'success',
                        'partial_match' => 'warning',
                        'mismatch' => 'danger',
                        'pending' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('variance_amount')
                    ->label('Selisih')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('variance_percent')
                    ->label('Selisih %')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 2) . '%' : '-')
                    ->sortable(),
                TextColumn::make('resolution_status')
                    ->label('Resolusi')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'open' => 'Terbuka',
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                        'resolved' => 'Terselesaikan',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'open' => 'warning',
                        'accepted' => 'info',
                        'rejected' => 'danger',
                        'resolved' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('match_status')
                    ->label('Status Pencocokan')
                    ->options([
                        'pending' => 'Menunggu',
                        'matched' => 'Cocok',
                        'partial_match' => 'Cocok Sebagian',
                        'mismatch' => 'Tidak Cocok',
                    ]),
                SelectFilter::make('resolution_status')
                    ->label('Resolusi')
                    ->options([
                        'open' => 'Terbuka',
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                        'resolved' => 'Terselesaikan',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('run_match')
                    ->label('Jalankan Matching')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->action(function ($record) {
                        $service = app(\App\Services\ThreeWayMatchService::class);
                        $results = $service->performMatch(
                            $record->purchaseOrder,
                            $record->goodsReceipt,
                            $record->invoice
                        );
                        $record->update(array_merge($results, [
                            'matched_by' => auth()->id(),
                            'matched_at' => now(),
                        ]));
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Jalankan 3-Way Matching')
                    ->modalDescription('Akan membandingkan PO, Penerimaan Barang, dan Faktur untuk mendeteksi selisih.')
                    ->modalSubmitActionLabel('Jalankan'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

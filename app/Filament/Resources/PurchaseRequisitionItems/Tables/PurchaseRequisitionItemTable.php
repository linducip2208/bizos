<?php

namespace App\Filament\Resources\PurchaseRequisitionItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchaseRequisitionItemTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchaseRequisition.pr_number')
                    ->label('No. PR')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('item_name')
                    ->label('Nama Item')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('specification')
                    ->label('Spesifikasi')
                    ->limit(30)
                    ->placeholder('-'),
                TextColumn::make('unit')
                    ->label('Satuan')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('estimated_price')
                    ->label('Estimasi Harga')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('purchase_requisition_id')
                    ->label('PR')
                    ->relationship('purchaseRequisition', 'pr_number'),
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
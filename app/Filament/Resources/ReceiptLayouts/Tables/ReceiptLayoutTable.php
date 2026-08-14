<?php

namespace App\Filament\Resources\ReceiptLayouts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReceiptLayoutTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pos_receipt' => 'Struk POS',
                        'invoice' => 'Invoice',
                        'label' => 'Label',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pos_receipt' => 'info',
                        'invoice' => 'success',
                        'label' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('font_size')
                    ->label('Font')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'small' => 'Kecil',
                        'medium' => 'Sedang',
                        'large' => 'Besar',
                        default => $state,
                    }),
                IconColumn::make('show_logo')
                    ->label('Logo')
                    ->boolean(),
                IconColumn::make('show_qr')
                    ->label('QR')
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label('Utama')
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

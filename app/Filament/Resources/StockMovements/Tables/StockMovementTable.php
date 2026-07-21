<?php

namespace App\Filament\Resources\StockMovements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class StockMovementTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('variant.name')
                    ->label('Varian')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('warehouse.name')
                    ->label('Gudang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('movement_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        'transfer' => 'Transfer',
                        'adjustment' => 'Penyesuaian',
                        'opname' => 'Opname',
                        'return' => 'Retur',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        'transfer' => 'info',
                        'adjustment' => 'warning',
                        'opname' => 'gray',
                        'return' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('reference_type')
                    ->label('Referensi')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('quantity_in')
                    ->label('Masuk')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('quantity_out')
                    ->label('Keluar')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('running_quantity')
                    ->label('Qty Berjalan')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('movement_date')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('movement_date', 'desc')
            ->filters([
                SelectFilter::make('movement_type')
                    ->label('Tipe')
                    ->options([
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        'transfer' => 'Transfer',
                        'adjustment' => 'Penyesuaian',
                        'opname' => 'Opname',
                        'return' => 'Retur',
                    ]),
                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name'),
                SelectFilter::make('warehouse_id')
                    ->label('Gudang')
                    ->relationship('warehouse', 'name'),
                Filter::make('movement_date')
                    ->label('Tanggal')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('movement_date', '>=', $date))
                            ->when($data['until'], fn (Builder $query, $date): Builder => $query->whereDate('movement_date', '<=', $date));
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

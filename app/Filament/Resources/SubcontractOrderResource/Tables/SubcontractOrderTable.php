<?php

namespace App\Filament\Resources\SubcontractOrderResource\Tables;

use App\Models\SubcontractOrder;
use App\Services\ManufacturingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class SubcontractOrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity_sent')
                    ->label('Qty Kirim')
                    ->sortable(),
                TextColumn::make('quantity_received')
                    ->label('Qty Terima')
                    ->sortable(),
                TextColumn::make('quantity_rejected')
                    ->label('Qty Tolak')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'warning',
                        'in_progress' => 'info',
                        'received' => 'success',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'draft' => 'Draft',
                        'sent' => 'Dikirim',
                        'in_progress' => 'Diproses',
                        'received' => 'Diterima',
                        'completed' => 'Selesai',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('expected_return')
                    ->label('Estimasi Kembali')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('actual_return')
                    ->label('Kembali Aktual')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('receive')
                    ->label('Terima dari Subkon')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('success')
                    ->visible(fn(SubcontractOrder $record) => $record->status === 'sent')
                    ->action(function (SubcontractOrder $record) {
                        app(ManufacturingService::class)->receiveFromSubcontract($record);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Penerimaan')
                    ->modalDescription('Tandai subkontrak ini sebagai diterima?'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\ProductionOrders\Tables;

use App\Models\ProductionOrder;
use App\Services\ManufacturingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class ProductionOrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('po_number')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('planned_quantity')
                    ->label('Qty Plan')
                    ->sortable(),
                TextColumn::make('produced_quantity')
                    ->label('Qty Hasil')
                    ->sortable(),
                TextColumn::make('rejected_quantity')
                    ->label('Qty Reject')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'draft' => 'gray',
                        'planned' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'draft' => 'Draft',
                        'planned' => 'Planned',
                        'in_progress' => 'In Progress',
                        'completed' => 'Selesai',
                        'cancelled' => 'Batal',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('planned_start')
                    ->label('Mulai Plan')
                    ->datetime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('planned_end')
                    ->label('Selesai Plan')
                    ->datetime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('materials_count')
                    ->label('Material')
                    ->counts('materials'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('mulai_produksi')
                    ->label('Mulai Produksi')
                    ->icon(Heroicon::OutlinedPlay)
                    ->color('success')
                    ->visible(fn(ProductionOrder $record) => $record->status === 'planned')
                    ->action(function (ProductionOrder $record) {
                        app(ManufacturingService::class)->startProduction($record);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Mulai Produksi')
                    ->modalDescription('Mulai production order ini? Material akan dikonsumsi dari stok.'),
                Action::make('jadwalkan')
                    ->label('Jadwalkan')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->color('warning')
                    ->visible(fn(ProductionOrder $record) => in_array($record->status, ['draft', 'planned']))
                    ->action(function (ProductionOrder $record) {
                        app(ManufacturingService::class)->generateSchedule($record, 'forward');
                    })
                    ->successNotificationTitle('Jadwal produksi berhasil digenerate.'),
                Action::make('selesai_produksi')
                    ->label('Selesai Produksi')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('indigo')
                    ->visible(fn(ProductionOrder $record) => $record->status === 'in_progress')
                    ->form([
                        TextInput::make('produced_quantity')
                            ->label('Qty Berhasil')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required()
                            ->default(fn(ProductionOrder $record) => $record->planned_quantity),
                        TextInput::make('rejected_quantity')
                            ->label('Qty Reject')
                            ->numeric()
                            ->inputMode('decimal')
                            ->default(0),
                    ])
                    ->action(function (ProductionOrder $record, array $data) {
                        app(ManufacturingService::class)->completeProduction(
                            $record,
                            (float) $data['produced_quantity'],
                            (float) $data['rejected_quantity']
                        );
                    })
                    ->successNotificationTitle('Produksi selesai. Stok barang jadi bertambah, material berkurang.'),
                Action::make('kirim_subkon')
                    ->label('Kirim ke Subkon')
                    ->icon(Heroicon::OutlinedTruck)
                    ->color('gray')
                    ->visible(fn(ProductionOrder $record) => in_array($record->status, ['planned', 'in_progress']))
                    ->form([
                        \Filament\Forms\Components\Select::make('supplier_id')
                            ->label('Supplier Subkon')
                            ->relationship('company.suppliers', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (ProductionOrder $record, array $data) {
                        $sub = app(ManufacturingService::class)->sendToSubcontract($record, (int) $data['supplier_id']);
                    })
                    ->successNotificationTitle('Production order dikirim ke subkon.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
<?php

namespace App\Filament\Resources\BillOfMaterialResource\Tables;

use App\Models\BillOfMaterial;
use App\Services\ManufacturingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BillOfMaterialTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama BOM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('revision')
                    ->label('Revisi')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Output/Batch')
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Satuan'),
                TextColumn::make('bom_items_count')
                    ->label('Komponen')
                    ->counts('bomItems'),
                TextColumn::make('effective_date')
                    ->label('Tgl Efektif')
                    ->date('d M Y')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('copy_bom')
                    ->label('Copy BOM')
                    ->icon(Heroicon::OutlinedClipboard)
                    ->color('gray')
                    ->action(function (BillOfMaterial $record) {
                        $newBom = $record->replicate();
                        $newBom->name = $record->name . ' (Copy)';
                        $newBom->revision = '1.0';
                        $newBom->save();

                        foreach ($record->bomItems as $item) {
                            $item->replicate()->fill(['bom_id' => $newBom->id])->save();
                        }

                        return redirect(BillOfMaterialResource::getUrl('edit', ['record' => $newBom]));
                    }),
                Action::make('mrp_view')
                    ->label('MRP View')
                    ->icon(Heroicon::OutlinedCalculator)
                    ->color('indigo')
                    ->modalHeading('MRP Calculation')
                    ->modalDescription(function (BillOfMaterial $record) {
                        $svc = app(ManufacturingService::class);
                        $mrp = $svc->calculateMrp($record->product_id, 30);
                        $table = "| Tanggal | Gross | Receipts | On Hand | Net Req |\n";
                        $table .= "|---------|-------|----------|---------|----------|\n";
                        foreach (array_slice($mrp['days'], 0, 14) as $d) {
                            $table .= "| {$d['date']} | {$d['gross_requirements']} | {$d['scheduled_receipts']} | {$d['projected_on_hand']} | {$d['net_requirements']} |\n";
                        }
                        return $table;
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

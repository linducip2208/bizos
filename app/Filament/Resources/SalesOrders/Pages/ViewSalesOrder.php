<?php

namespace App\Filament\Resources\SalesOrders\Pages;

use App\Filament\Resources\SalesOrders\SalesOrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\RepeatableEntry;
use Filament\Schemas\Components\Section as InfoSection;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class ViewSalesOrder extends ViewRecord
{
    protected static string $resource = SalesOrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                InfoSection::make('Informasi Sales Order')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('so_number')->label('No. SO'),
                        TextEntry::make('client.name')->label('Klien'),
                        TextEntry::make('quotation.quotation_number')->label('Quotation'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'draft' => 'gray', 'confirmed' => 'info', 'in_progress' => 'warning',
                                'shipped' => 'primary', 'delivered' => 'success', 'invoiced' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('order_date')->label('Tgl Order')->date('d M Y'),
                        TextEntry::make('expected_delivery')->label('Estimasi Kirim')->date('d M Y'),
                        TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                        TextEntry::make('tax')->label('Pajak')->money('IDR'),
                        TextEntry::make('discount')->label('Diskon')->money('IDR'),
                        TextEntry::make('shipping_cost')->label('Biaya Kirim')->money('IDR'),
                        TextEntry::make('total')->label('Total')->money('IDR'),
                        TextEntry::make('notes')->label('Catatan')->columnSpanFull(),
                    ]),
                InfoSection::make('Item')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')->label('Produk'),
                                TextEntry::make('description')->label('Deskripsi'),
                                TextEntry::make('quantity')->label('Qty'),
                                TextEntry::make('unit_price')->label('Harga')->money('IDR'),
                                TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                            ])
                            ->columns(5),
                    ]),
            ]);
    }
}

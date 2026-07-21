<?php

namespace App\Filament\Resources\SalesOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SalesOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Sales Order')
                    ->columns(2)
                    ->schema([
                        Select::make('client_id')
                            ->label('Klien')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('quotation_id')
                            ->label('Quotation')
                            ->relationship('quotation', 'quotation_number')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DatePicker::make('order_date')
                            ->label('Tanggal Order')
                            ->required()
                            ->default(now()),
                        DatePicker::make('expected_delivery')
                            ->label('Estimasi Pengiriman')
                            ->nullable(),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('draft')
                            ->options([
                                'draft' => 'Draft',
                                'confirmed' => 'Dikonfirmasi',
                                'in_progress' => 'Diproses',
                                'shipped' => 'Dikirim',
                                'delivered' => 'Terkirim',
                                'invoiced' => 'Tertagih',
                                'cancelled' => 'Dibatalkan',
                            ]),
                        TextInput::make('shipping_cost')
                            ->label('Biaya Kirim')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('createdBy', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Item Sales Order')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->label('Item')
                            ->columns(6)
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Item')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                TextInput::make('description')
                                    ->label('Deskripsi')
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                                TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required(),
                                TextInput::make('tax_rate')
                                    ->label('Pajak %')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%'),
                                TextInput::make('discount_percent')
                                    ->label('Diskon %')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%'),
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $qty = (float) ($data['quantity'] ?? 1);
                                $price = (float) ($data['unit_price'] ?? 0);
                                $disc = (float) ($data['discount_percent'] ?? 0);
                                $data['subtotal'] = round($qty * $price * (1 - $disc / 100), 2);
                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                $qty = (float) ($data['quantity'] ?? 1);
                                $price = (float) ($data['unit_price'] ?? 0);
                                $disc = (float) ($data['discount_percent'] ?? 0);
                                $data['subtotal'] = round($qty * $price * (1 - $disc / 100), 2);
                                return $data;
                            }),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pesanan')
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Select::make('pr_id')
                            ->label('No. PR')
                            ->relationship('purchaseRequisition', 'pr_number')
                            ->preload()
                            ->searchable()
                            ->nullable(),
                        Select::make('warehouse_id')
                            ->label('Gudang')
                            ->relationship('warehouse', 'name')
                            ->preload()
                            ->searchable()
                            ->nullable(),
                        DatePicker::make('order_date')
                            ->label('Tanggal Pesan')
                            ->required()
                            ->default(now()),
                        DatePicker::make('expected_date')
                            ->label('Tanggal Diharapkan')
                            ->nullable(),
                    ])->columns(3),

                Section::make('Rincian Biaya')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('discount_amount')
                            ->label('Diskon')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('tax_amount')
                            ->label('Pajak')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('shipping_cost')
                            ->label('Biaya Kirim')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(3),

                Section::make('Informasi Tambahan')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Textarea::make('terms_conditions')
                            ->label('Syarat & Ketentuan')
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Terkirim',
                                'approved' => 'Disetujui',
                                'partially_received' => 'Diterima Sebagian',
                                'received' => 'Diterima',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('draft')
                            ->required(),
                        Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('creator', 'full_name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approver', 'full_name')
                            ->preload()
                            ->searchable()
                            ->nullable(),
                    ])->columns(2),
            ]);
    }
}
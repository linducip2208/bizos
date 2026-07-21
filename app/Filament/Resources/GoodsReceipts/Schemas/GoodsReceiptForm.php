<?php

namespace App\Filament\Resources\GoodsReceipts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GoodsReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penerimaan')
                    ->schema([
                        Select::make('purchase_order_id')
                            ->label('No. PO')
                            ->relationship('purchaseOrder', 'po_number')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->live(),
                        Select::make('warehouse_id')
                            ->label('Gudang')
                            ->relationship('warehouse', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Select::make('received_by')
                            ->label('Diterima Oleh')
                            ->relationship('receiver', 'full_name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        DatePicker::make('receipt_date')
                            ->label('Tanggal Terima')
                            ->required()
                            ->default(now()),
                        TextInput::make('delivery_note')
                            ->label('Surat Jalan')
                            ->maxLength(100),
                        TextInput::make('invoice_number')
                            ->label('No. Invoice')
                            ->maxLength(100),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'posted' => 'Diposting',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('draft')
                            ->required(),
                    ]),
            ]);
    }
}

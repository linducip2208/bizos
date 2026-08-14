<?php

namespace App\Filament\Resources\ThreeWayMatches\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ThreeWayMatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Dokumen Referensi')
                    ->columns(3)
                    ->schema([
                        Select::make('purchase_order_id')
                            ->label('Purchase Order')
                            ->relationship('purchaseOrder', 'po_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('goods_receipt_id', null)),
                        Select::make('goods_receipt_id')
                            ->label('Penerimaan Barang')
                            ->relationship('goodsReceipt', 'grn_number')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->options(function (callable $get) {
                                $poId = $get('purchase_order_id');
                                if (!$poId) return [];
                                return \App\Models\GoodsReceipt::where('purchase_order_id', $poId)
                                    ->pluck('grn_number', 'id');
                            }),
                        Select::make('invoice_id')
                            ->label('Faktur Pembelian')
                            ->relationship('invoice', 'invoice_number')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->options(function (callable $get) {
                                return \App\Models\Invoice::where('invoice_type', 'purchase')
                                    ->pluck('invoice_number', 'id');
                            }),
                    ]),

                Section::make('Hasil Pencocokan')
                    ->columns(3)
                    ->visible(fn (callable $get) => $get('match_status'))
                    ->schema([
                        Placeholder::make('match_status_label')
                            ->label('Status Pencocokan')
                            ->content(function (callable $get) {
                                $status = $get('match_status');
                                return match ($status) {
                                    'matched' => '✅ Cocok',
                                    'partial_match' => '⚠️ Cocok Sebagian',
                                    'mismatch' => '❌ Tidak Cocok',
                                    'pending' => '⏳ Menunggu',
                                    default => $status,
                                };
                            }),
                        TextInput::make('po_total')
                            ->label('Total PO')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        TextInput::make('gr_total')
                            ->label('Total Penerimaan')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        TextInput::make('invoice_total')
                            ->label('Total Faktur')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        TextInput::make('variance_amount')
                            ->label('Selisih (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        TextInput::make('variance_percent')
                            ->label('Selisih (%)')
                            ->suffix('%')
                            ->disabled(),
                        Toggle::make('quantity_match')
                            ->label('Kuantitas Cocok')
                            ->disabled(),
                        Toggle::make('price_match')
                            ->label('Harga Cocok')
                            ->disabled(),
                        Toggle::make('total_match')
                            ->label('Total Cocok')
                            ->disabled(),
                    ]),

                Section::make('Resolusi')
                    ->columns(2)
                    ->schema([
                        Select::make('resolution_status')
                            ->label('Status Resolusi')
                            ->options([
                                'open' => 'Terbuka',
                                'accepted' => 'Diterima',
                                'rejected' => 'Ditolak',
                                'resolved' => 'Terselesaikan',
                            ])
                            ->default('open')
                            ->required(),
                        Textarea::make('resolution_notes')
                            ->label('Catatan Resolusi')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

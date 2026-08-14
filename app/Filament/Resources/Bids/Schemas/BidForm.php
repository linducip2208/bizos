<?php

namespace App\Filament\Resources\Bids\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BidForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penawaran')
                    ->columns(2)
                    ->schema([
                        Select::make('rfq_id')
                            ->label('RFQ')
                            ->relationship('rfq', 'rfq_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('bid_number')
                            ->label('Nomor Penawaran')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Terkirim',
                                'shortlisted' => 'Terpilih',
                                'accepted' => 'Diterima',
                                'rejected' => 'Ditolak',
                            ])
                            ->required()
                            ->default('draft'),
                        TextInput::make('total_amount')
                            ->label('Total Penawaran')
                            ->numeric()
                            ->nullable(),
                        Select::make('currency_id')
                            ->label('Mata Uang')
                            ->relationship('currency', 'code')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('delivery_lead_time_days')
                            ->label('Estimasi Pengiriman (hari)')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('validity_days')
                            ->label('Masa Berlaku (hari)')
                            ->numeric()
                            ->default(30),
                        DateTimePicker::make('submitted_at')
                            ->label('Tanggal Kirim')
                            ->nullable(),
                    ]),
                Section::make('Evaluasi')
                    ->columns(2)
                    ->schema([
                        Select::make('evaluated_by')
                            ->label('Dievaluasi Oleh')
                            ->relationship('evaluator', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DateTimePicker::make('evaluated_at')
                            ->label('Tanggal Evaluasi')
                            ->nullable(),
                        TextInput::make('evaluation_score')
                            ->label('Skor Evaluasi')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->nullable()
                            ->helperText('Skor 0-100'),
                        Textarea::make('evaluation_notes')
                            ->label('Catatan Evaluasi')
                            ->nullable(),
                    ]),
                Section::make('Catatan & Dokumen')
                    ->columns(1)
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->nullable()
                            ->columnSpanFull(),
                        Textarea::make('documents')
                            ->label('Dokumen (JSON)')
                            ->nullable()
                            ->rows(3)
                            ->helperText('Masukkan dalam format JSON, contoh: {"proposal":"url","katalog":"url"}')
                            ->columnSpanFull(),
                    ]),
                Section::make('Item Penawaran')
                    ->columns(1)
                    ->schema([
                        Repeater::make('items')
                            ->label('Item')
                            ->relationship()
                            ->schema([
                                Select::make('rfq_item_id')
                                    ->label('Item RFQ')
                                    ->relationship('rfqItem', 'description')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                TextInput::make('description')
                                    ->label('Deskripsi')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('quantity')
                                    ->label('Kuantitas')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('total_price')
                                    ->label('Total Harga')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('delivery_days')
                                    ->label('Estimasi Pengiriman (hari)')
                                    ->numeric()
                                    ->nullable(),
                                Textarea::make('notes')
                                    ->label('Catatan Item')
                                    ->nullable(),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Item')
                            ->defaultItems(1),
                    ]),
            ]);
    }
}

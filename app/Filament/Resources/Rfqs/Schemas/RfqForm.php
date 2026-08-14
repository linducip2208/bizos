<?php

namespace App\Filament\Resources\Rfqs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RfqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi RFQ')
                    ->columns(2)
                    ->schema([
                        TextInput::make('rfq_number')
                            ->label('Nomor RFQ')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('title')
                            ->label('Judul RFQ')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Terkirim',
                                'open' => 'Terbuka',
                                'closed' => 'Tertutup',
                                'awarded' => 'Diberikan',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('draft'),
                        TextInput::make('supplier_category')
                            ->label('Kategori Supplier')
                            ->nullable()
                            ->maxLength(255),
                        Select::make('currency_id')
                            ->label('Mata Uang')
                            ->relationship('currency', 'code')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DateTimePicker::make('submission_deadline')
                            ->label('Batas Pengumpulan')
                            ->required(),
                        DatePicker::make('expected_delivery_date')
                            ->label('Tanggal Pengiriman Diharapkan')
                            ->nullable(),
                    ]),
                Section::make('Deskripsi & Catatan')
                    ->columns(1)
                    ->schema([
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->nullable()
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Approval')
                    ->columns(2)
                    ->schema([
                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DateTimePicker::make('approved_at')
                            ->label('Tanggal Disetujui')
                            ->nullable(),
                    ]),
                Section::make('Item RFQ')
                    ->columns(1)
                    ->schema([
                        Repeater::make('items')
                            ->label('Item')
                            ->relationship()
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
                                    ->maxLength(255),
                                TextInput::make('quantity')
                                    ->label('Kuantitas')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),
                                Select::make('unit_id')
                                    ->label('Satuan')
                                    ->relationship('unit', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                Textarea::make('specifications')
                                    ->label('Spesifikasi (JSON)')
                                    ->nullable()
                                    ->rows(3)
                                    ->helperText('Masukkan dalam format JSON, contoh: {"warna":"merah","ukuran":"XL"}'),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Item')
                            ->defaultItems(1),
                    ]),
                Section::make('Supplier yang Diundang')
                    ->columns(1)
                    ->schema([
                        Repeater::make('rfqSuppliers')
                            ->label('Supplier')
                            ->relationship()
                            ->schema([
                                Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'invited' => 'Diundang',
                                        'responded' => 'Merespon',
                                        'declined' => 'Menolak',
                                    ])
                                    ->default('invited')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Supplier')
                            ->defaultItems(1),
                    ]),
            ]);
    }
}

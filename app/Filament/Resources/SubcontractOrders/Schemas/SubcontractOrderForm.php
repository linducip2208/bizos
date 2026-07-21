<?php

namespace App\Filament\Resources\SubcontractOrders\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SubcontractOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Subkontrak')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('quantity_sent')
                            ->label('Qty Dikirim')
                            ->numeric()
                            ->inputMode('decimal')
                            ->default(0),
                        TextInput::make('quantity_received')
                            ->label('Qty Diterima')
                            ->numeric()
                            ->inputMode('decimal')
                            ->default(0),
                        TextInput::make('quantity_rejected')
                            ->label('Qty Ditolak')
                            ->numeric()
                            ->inputMode('decimal')
                            ->default(0),
                        DatePicker::make('sent_date')
                            ->label('Tanggal Kirim')
                            ->native(false),
                        DatePicker::make('expected_return')
                            ->label('Estimasi Kembali')
                            ->native(false),
                        DatePicker::make('actual_return')
                            ->label('Tanggal Kembali Aktual')
                            ->native(false),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Dikirim',
                                'in_progress' => 'Diproses',
                                'received' => 'Diterima',
                                'completed' => 'Selesai',
                            ])
                            ->default('draft')
                            ->required(),
                        TextInput::make('cost')
                            ->label('Biaya (Rp)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('Rp')
                            ->default(0),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
<?php

namespace App\Filament\Resources\PosVouchers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PosVoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Voucher')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('code')
                            ->label('Kode Voucher')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama Voucher')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'percentage' => 'Persentase',
                                'fixed' => 'Nominal',
                            ])
                            ->required(),
                        TextInput::make('value')
                            ->label('Nilai')
                            ->numeric()
                            ->required()
                            ->hint(fn ($get) => $get('type') === 'percentage' ? '%' : 'Rp'),
                        TextInput::make('min_purchase')
                            ->label('Minimal Pembelian')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('max_discount')
                            ->label('Maksimal Diskon')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                        TextInput::make('usage_limit')
                            ->label('Batas Pemakaian')
                            ->numeric()
                            ->integer()
                            ->nullable(),
                        TextInput::make('used_count')
                            ->label('Terpakai')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Tanggal Berakhir')
                            ->afterOrEqual('start_date')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
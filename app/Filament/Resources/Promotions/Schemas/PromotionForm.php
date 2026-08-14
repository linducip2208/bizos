<?php

namespace App\Filament\Resources\Promotions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Promosi')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Promosi')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Tipe')
                            ->required()
                            ->options([
                                'discount_percent' => 'Diskon Persen',
                                'discount_amount' => 'Diskon Nominal',
                                'buy_x_get_y' => 'Beli X Gratis Y',
                                'bundle' => 'Bundle (Beli A + B)',
                                'free_shipping' => 'Gratis Ongkir',
                            ]),
                        DatePicker::make('start_date')
                            ->label('Mulai')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Selesai')
                            ->required()
                            ->after('start_date'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
                Section::make('Aturan Diskon Otomatis')
                    ->columns(2)
                    ->schema([
                        Select::make('discount_type')
                            ->label('Jenis Diskon')
                            ->options([
                                'percentage' => 'Persen (%)',
                                'fixed' => 'Nominal (Rp)',
                            ])
                            ->nullable(),
                        TextInput::make('discount_value')
                            ->label('Nilai Diskon')
                            ->numeric()
                            ->nullable()
                            ->hint('Persen atau nominal tergantung jenis'),
                        TextInput::make('min_purchase')
                            ->label('Minimal Pembelian')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Select::make('applies_to')
                            ->label('Berlaku Untuk')
                            ->options([
                                'all' => 'Semua Produk',
                                'products' => 'Produk Tertentu',
                                'category' => 'Kategori Tertentu',
                            ])
                            ->default('all')
                            ->live(),
                        Select::make('applies_to_ids')
                            ->label('Target')
                            ->multiple()
                            ->searchable()
                            ->options(function ($get) {
                                if ($get('applies_to') === 'category') {
                                    return \App\Models\ProductCategory::query()
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all();
                                }

                                return \App\Models\Product::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->visible(fn ($get) => in_array($get('applies_to'), ['products', 'category']))
                            ->helperText('Pilih produk atau kategori target diskon'),
                        Toggle::make('auto_apply')
                            ->label('Auto Apply')
                            ->default(false)
                            ->helperText('Terapkan otomatis di kasir'),
                        Toggle::make('stacking_allowed')
                            ->label('Boleh Ditumpuk')
                            ->default(false)
                            ->helperText('Boleh digabung dengan kupon'),
                        Textarea::make('config')
                            ->label('Konfigurasi (JSON)')
                            ->rows(4)
                            ->hint('Contoh: {"percent": 10, "max_discount": 50000, "buy_qty": 2, "get_qty": 1}')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

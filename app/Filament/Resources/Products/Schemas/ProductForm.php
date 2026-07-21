<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Produk')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('code')
                            ->label('Kode Produk')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->maxLength(20)
                            ->default('pcs'),
                        TextInput::make('purchase_price')
                            ->label('Harga Beli')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('selling_price')
                            ->label('Harga Jual')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        TextInput::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->default(0),
                        TextInput::make('min_stock')
                            ->label('Stok Minimum')
                            ->numeric()
                            ->default(0),
                        TextInput::make('max_stock')
                            ->label('Stok Maksimum')
                            ->numeric()
                            ->default(0),
                        FileUpload::make('photo')
                            ->label('Foto Produk')
                            ->image()
                            ->directory('products')
                            ->imagePreviewHeight('200')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_taxable')
                            ->label('Kena Pajak')
                            ->default(false),
                        TextInput::make('tax_rate')
                            ->label('Tarif Pajak (%)')
                            ->numeric()
                            ->suffix('%')
                            ->visible(fn ($get) => $get('is_taxable')),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
                Section::make('Informasi Obat')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_medicine')
                            ->label('Produk Obat')
                            ->default(false)
                            ->live(),
                        TextInput::make('active_ingredient')
                            ->label('Zat Aktif')
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('is_medicine')),
                        Select::make('dosage_form')
                            ->label('Bentuk Sediaan')
                            ->options([
                                'tablet' => 'Tablet',
                                'capsule' => 'Kapsul',
                                'syrup' => 'Sirup',
                                'injection' => 'Injeksi',
                                'ointment' => 'Salep',
                                'drop' => 'Tetes',
                                'inhaler' => 'Inhaler',
                                'powder' => 'Serbuk',
                            ])
                            ->visible(fn ($get) => $get('is_medicine')),
                        TextInput::make('strength')
                            ->label('Kekuatan')
                            ->maxLength(100)
                            ->placeholder('500mg, 10mg/ml')
                            ->visible(fn ($get) => $get('is_medicine')),
                        TextInput::make('registration_number')
                            ->label('No. BPOM')
                            ->maxLength(100)
                            ->visible(fn ($get) => $get('is_medicine')),
                        Toggle::make('requires_prescription')
                            ->label('Perlu Resep')
                            ->default(false)
                            ->visible(fn ($get) => $get('is_medicine')),
                        Select::make('drug_category')
                            ->label('Golongan Obat')
                            ->options([
                                'obat_bebas' => 'Obat Bebas',
                                'obat_bebas_terbatas' => 'Obat Bebas Terbatas',
                                'obat_keras' => 'Obat Keras',
                                'narkotika' => 'Narkotika',
                                'psikotropika' => 'Psikotropika',
                            ])
                            ->visible(fn ($get) => $get('is_medicine')),
                        TextInput::make('storage_requirement')
                            ->label('Penyimpanan')
                            ->maxLength(255)
                            ->placeholder('Sejuk 15-25°C')
                            ->visible(fn ($get) => $get('is_medicine')),
                    ]),
            ]);
    }
}

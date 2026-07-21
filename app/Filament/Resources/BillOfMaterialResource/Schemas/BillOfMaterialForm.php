<?php

namespace App\Filament\Resources\BillOfMaterialResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BillOfMaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi BOM')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('product_id')
                            ->label('Produk Jadi (Finished Good)')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama BOM')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('revision')
                            ->label('Revisi')
                            ->default('1.0')
                            ->maxLength(20),
                        TextInput::make('quantity')
                            ->label('Output per Batch')
                            ->numeric()
                            ->inputMode('decimal')
                            ->default(1),
                        TextInput::make('unit')
                            ->label('Satuan Output')
                            ->default('pcs')
                            ->maxLength(20),
                        DatePicker::make('effective_date')
                            ->label('Tanggal Efektif')
                            ->native(false),
                        DatePicker::make('obsolete_date')
                            ->label('Tanggal Kadaluarsa')
                            ->native(false),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

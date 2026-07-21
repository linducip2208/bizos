<?php

namespace App\Filament\Resources\CurrencyResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Mata Uang')
                    ->columns(3)
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode (3 Huruf)')
                            ->required()
                            ->maxLength(3)
                            ->unique('currencies', 'code', ignoreRecord: true)
                            ->placeholder('IDR')
                            ->helperText('Kode ISO 4217, contoh: IDR, USD, SGD'),
                        TextInput::make('name')
                            ->label('Nama Mata Uang')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Indonesian Rupiah'),
                        TextInput::make('symbol')
                            ->label('Simbol')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('Rp'),
                    ]),
                Section::make('Format & Kurs')
                    ->columns(4)
                    ->schema([
                        TextInput::make('exchange_rate')
                            ->label('Nilai Tukar (terhadap mata uang dasar)')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->inputMode('decimal'),
                        Toggle::make('is_base')
                            ->label('Mata Uang Dasar')
                            ->helperText('Hanya satu mata uang yang bisa menjadi mata uang dasar')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        TextInput::make('decimal_places')
                            ->label('Desimal')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(6)
                            ->default(2),
                        TextInput::make('thousands_separator')
                            ->label('Pemisah Ribuan')
                            ->maxLength(1)
                            ->default('.'),
                        TextInput::make('decimal_separator')
                            ->label('Pemisah Desimal')
                            ->maxLength(1)
                            ->default(','),
                        TextInput::make('format')
                            ->label('Format')
                            ->maxLength(20)
                            ->default('1.234,56')
                            ->helperText('Contoh format: 1.234,56 atau 1,234.56'),
                    ]),
            ]);
    }
}

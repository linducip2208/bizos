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
                        Textarea::make('config')
                            ->label('Konfigurasi (JSON)')
                            ->rows(4)
                            ->hint('Contoh: {"percent": 10, "max_discount": 50000}')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

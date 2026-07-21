<?php

namespace App\Filament\Resources\RoutingOperationResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RoutingOperationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Routing Operasi')
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('bom_id')
                            ->label('BOM (Opsional)')
                            ->relationship('bom', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('work_center_id')
                            ->label('Work Center')
                            ->relationship('workCenter', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('operation_name')
                            ->label('Nama Operasi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sequence')
                            ->label('Urutan')
                            ->integer()
                            ->required()
                            ->default(1),
                        TextInput::make('setup_time_minutes')
                            ->label('Waktu Setup (menit)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->default(0),
                        TextInput::make('run_time_minutes_per_unit')
                            ->label('Waktu Proses/Unit (menit)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->default(0),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

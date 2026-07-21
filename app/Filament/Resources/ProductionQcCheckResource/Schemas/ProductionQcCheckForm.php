<?php

namespace App\Filament\Resources\ProductionQcCheckResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductionQcCheckForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi QC Check')
                    ->columns(2)
                    ->schema([
                        Select::make('production_order_id')
                            ->label('Production Order')
                            ->relationship('productionOrder', 'po_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('check_type')
                            ->label('Tipe Pengecekan')
                            ->options([
                                'incoming_material' => 'Incoming Material',
                                'in_process' => 'In-Process',
                                'final' => 'Final',
                            ])
                            ->required()
                            ->default('final'),
                        TextInput::make('parameter')
                            ->label('Parameter')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('specification')
                            ->label('Spesifikasi')
                            ->rows(2),
                        Select::make('result')
                            ->label('Hasil')
                            ->options([
                                'pass' => 'Lolos',
                                'fail' => 'Gagal',
                                'conditional' => 'Bersyarat',
                            ])
                            ->nullable(),
                        Select::make('checked_by')
                            ->label('Diperiksa Oleh')
                            ->relationship('checker', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DateTimePicker::make('checked_at')
                            ->label('Waktu Periksa')
                            ->native(false)
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\WaterUsages\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WaterUsageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Pemakaian Air')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DatePicker::make('record_date')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),
                        Select::make('source')
                            ->label('Sumber Air')
                            ->options([
                                'municipal' => 'PDAM',
                                'well' => 'Sumur',
                                'rainwater' => 'Air Hujan',
                                'recycled' => 'Daur Ulang',
                                'surface_water' => 'Air Permukaan',
                            ])
                            ->required(),
                        TextInput::make('quantity_m3')
                            ->label('Jumlah (m3)')
                            ->numeric()
                            ->required(),
                        Select::make('purpose')
                            ->label('Tujuan')
                            ->options([
                                'production' => 'Produksi',
                                'sanitation' => 'Sanitasi',
                                'cooling' => 'Pendingin',
                                'irrigation' => 'Irigasi',
                                'domestic' => 'Domestik',
                            ])
                            ->required(),
                        TextInput::make('cost')
                            ->label('Biaya (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                        TextInput::make('meter_number')
                            ->label('Nomor Meter')
                            ->maxLength(50)
                            ->nullable(),
                        TextInput::make('meter_reading_start')
                            ->label('Meter Awal')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('meter_reading_end')
                            ->label('Meter Akhir')
                            ->numeric()
                            ->nullable(),
                        Toggle::make('is_recycled')
                            ->label('Air Daur Ulang')
                            ->default(false),
                        TextInput::make('recycled_percentage')
                            ->label('Persentase Daur Ulang (%)')
                            ->numeric()
                            ->suffix('%')
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

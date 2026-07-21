<?php

namespace App\Filament\Resources\AssetMutation\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssetMutationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Mutasi Aset')
                    ->columns(2)
                    ->schema([
                        Select::make('asset_id')
                            ->label('Aset')
                            ->relationship('asset', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('mutation_type')
                            ->label('Tipe Mutasi')
                            ->options([
                                'relocation' => 'Relokasi',
                                'assignment' => 'Penugasan',
                                'return' => 'Pengembalian',
                                'disposal' => 'Pembuangan',
                                'transfer' => 'Transfer',
                            ])
                            ->required(),
                        TextInput::make('from_location')
                            ->label('Dari Lokasi')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('to_location')
                            ->label('Ke Lokasi')
                            ->maxLength(255)
                            ->nullable(),
                        Select::make('from_employee_id')
                            ->label('Dari Karyawan')
                            ->relationship('fromEmployee', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('to_employee_id')
                            ->label('Ke Karyawan')
                            ->relationship('toEmployee', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DatePicker::make('mutation_date')
                            ->label('Tanggal Mutasi')
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

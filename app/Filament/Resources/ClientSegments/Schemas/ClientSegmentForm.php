<?php

namespace App\Filament\Resources\ClientSegments\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ClientSegmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Segment Klien')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Segment')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                        ColorPicker::make('color')
                            ->label('Warna')
                            ->default('#6366f1'),
                        KeyValue::make('criteria_json')
                            ->label('Kriteria')
                            ->keyLabel('Field')
                            ->valueLabel('Nilai')
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Kriteria'),
                    ]),
            ]);
    }
}
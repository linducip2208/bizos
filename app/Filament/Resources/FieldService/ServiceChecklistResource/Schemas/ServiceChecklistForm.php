<?php

namespace App\Filament\Resources\FieldService\ServiceChecklistResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Repeater;
use Filament\Schemas\Schema;

class ServiceChecklistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Checklist')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Checklist')
                            ->required()
                            ->maxLength(255),
                        Select::make('service_type')
                            ->label('Tipe Layanan')
                            ->required()
                            ->options([
                                'preventive' => 'Preventif',
                                'corrective' => 'Korektif',
                                'installation' => 'Instalasi',
                                'inspection' => 'Inspeksi',
                            ]),
                        Select::make('is_active')
                            ->label('Status')
                            ->required()
                            ->default(true)
                            ->options([
                                true => 'Aktif',
                                false => 'Nonaktif',
                            ]),
                    ]),
                Section::make('Item Checklist')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->label('Item Checklist')
                            ->schema([
                                TextInput::make('description')
                                    ->label('Deskripsi')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('is_required')
                                    ->label('Wajib')
                                    ->required()
                                    ->default(true)
                                    ->options([
                                        true => 'Ya',
                                        false => 'Tidak',
                                    ]),
                                TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(3)
                            ->orderColumn('sort_order')
                            ->defaultItems(0)
                            ->collapsible(),
                    ]),
            ]);
    }
}
<?php

namespace App\Filament\Resources\ModifierGroups\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ModifierGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Grup Modifier')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Grup')
                            ->required()
                            ->maxLength(255),
                        Select::make('selection_type')
                            ->label('Tipe Pilihan')
                            ->options([
                                'single' => 'Pilih Satu',
                                'multiple' => 'Pilih Banyak',
                            ])
                            ->required()
                            ->default('single'),
                        TextInput::make('min_selections')
                            ->label('Minimal Pilihan')
                            ->numeric()
                            ->default(0),
                        TextInput::make('max_selections')
                            ->label('Maksimal Pilihan')
                            ->numeric()
                            ->nullable(),
                        Toggle::make('is_required')
                            ->label('Wajib')
                            ->default(false),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Modifier')
                    ->columns(1)
                    ->schema([
                        Repeater::make('modifiers')
                            ->label('Modifier')
                            ->relationship()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Modifier')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('price')
                                    ->label('Harga Tambahan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0),
                                TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->default(0),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Modifier')
                            ->defaultItems(1),
                    ]),
            ]);
    }
}

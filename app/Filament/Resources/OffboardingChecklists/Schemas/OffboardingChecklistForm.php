<?php

namespace App\Filament\Resources\OffboardingChecklists\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OffboardingChecklistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Checklist')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Checklist')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_default')
                            ->label('Jadikan Default')
                            ->helperText('Checklist default digunakan saat proses offboarding karyawan'),
                    ]),

                Section::make('Item Checklist')
                    ->schema([
                        Repeater::make('items')
                            ->label('Daftar Item')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->addActionLabel('Tambah Item')
                            ->columns(3)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Item')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('assigned_role')
                                    ->label('PIC')
                                    ->options([
                                        'hr' => 'HR',
                                        'it' => 'IT',
                                        'finance' => 'Keuangan',
                                        'manager' => 'Manager',
                                        'employee' => 'Karyawan',
                                    ])
                                    ->required(),
                                Toggle::make('is_required')
                                    ->label('Wajib')
                                    ->default(true),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
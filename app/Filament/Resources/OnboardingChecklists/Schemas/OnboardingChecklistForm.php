<?php

namespace App\Filament\Resources\OnboardingChecklists\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OnboardingChecklistForm
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
                            ->helperText('Checklist default akan otomatis digunakan saat karyawan baru onboarding'),
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
                                TextInput::make('days_before_join')
                                    ->label('Hari Sebelum Join')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('0 = hari H, 3 = 3 hari sebelum join'),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Toggle::make('is_required')
                                    ->label('Wajib')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
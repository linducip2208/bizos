<?php

namespace App\Filament\Resources\Licenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LicenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Lisensi')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('module')
                            ->label('Modul')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nama modul yang dilisensikan'),
                        TextInput::make('license_key_encrypted')
                            ->label('License Key')
                            ->password()
                            ->required()
                            ->maxLength(65535),
                        TextInput::make('seats')
                            ->label('Jumlah Seat')
                            ->numeric()
                            ->default(10)
                            ->required(),
                        DatePicker::make('started_at')
                            ->label('Tanggal Mulai')
                            ->required(),
                        DatePicker::make('expires_at')
                            ->label('Tanggal Kadaluarsa')
                            ->nullable()
                            ->helperText('Kosongkan jika tidak ada masa berlaku'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'expired' => 'Kadaluarsa',
                                'suspended' => 'Ditangguhkan',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
            ]);
    }
}

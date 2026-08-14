<?php

namespace App\Filament\Resources\Printers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PrinterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Printer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Printer')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('cth: Printer Kasir 1'),
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Semua cabang'),
                        Select::make('printer_type')
                            ->label('Tipe Printer')
                            ->options([
                                'thermal_58' => 'Thermal 58 mm',
                                'thermal_80' => 'Thermal 80 mm',
                                'a4' => 'A4 / Inkjet-Laser',
                            ])
                            ->default('thermal_58')
                            ->required()
                            ->live(),
                        Select::make('connection_type')
                            ->label('Tipe Koneksi')
                            ->options([
                                'usb' => 'USB',
                                'network' => 'Jaringan (Network)',
                                'cloud' => 'Cloud Print',
                            ])
                            ->default('usb')
                            ->required()
                            ->live(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Nonaktif',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
                Section::make('Koneksi Jaringan')
                    ->description('Diisi jika tipe koneksi Jaringan (Network).')
                    ->columns(2)
                    ->schema([
                        TextInput::make('ip_address')
                            ->label('Alamat IP')
                            ->maxLength(45)
                            ->placeholder('cth: 192.168.1.100')
                            ->visible(fn ($get) => $get('connection_type') === 'network'),
                        TextInput::make('port')
                            ->label('Port')
                            ->numeric()
                            ->integer()
                            ->default(9100)
                            ->visible(fn ($get) => $get('connection_type') === 'network'),
                    ]),
                Section::make('Pengaturan Kertas')
                    ->columns(2)
                    ->schema([
                        Select::make('paper_width')
                            ->label('Lebar Kertas (mm)')
                            ->options([
                                58 => '58 mm',
                                80 => '80 mm',
                            ])
                            ->default(58)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('character_per_line', $state == 80 ? 48 : 32)),
                        TextInput::make('character_per_line')
                            ->label('Karakter Per Baris')
                            ->numeric()
                            ->integer()
                            ->default(32)
                            ->required(),
                        Toggle::make('is_default')
                            ->label('Jadikan Printer Utama')
                            ->helperText('Hanya satu printer utama per cabang.'),
                    ]),
            ]);
    }
}

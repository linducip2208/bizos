<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Klien')
                    ->columns(2)
                    ->schema([
                        TextInput::make('client_code')
                            ->label('Kode Klien')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Select::make('client_type')
                            ->label('Tipe Klien')
                            ->required()
                            ->default('individual')
                            ->options([
                                'individual' => 'Individu',
                                'company' => 'Perusahaan',
                                'government' => 'Pemerintah',
                            ]),
                        TextInput::make('industry')
                            ->label('Industri')
                            ->maxLength(255),
                        TextInput::make('tax_id')
                            ->label('NPWP')
                            ->maxLength(50),
                        TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label('Kota')
                            ->maxLength(100),
                        TextInput::make('province')
                            ->label('Provinsi')
                            ->maxLength(100),
                        TextInput::make('postal_code')
                            ->label('Kode Pos')
                            ->maxLength(10),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('aktif')
                            ->options([
                                'aktif' => 'Aktif',
                                'nonaktif' => 'Nonaktif',
                                'prospek' => 'Prospek',
                                'blacklist' => 'Blacklist',
                            ]),
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('clients/logos')
                            ->imagePreviewHeight('100')
                            ->maxSize(2048),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
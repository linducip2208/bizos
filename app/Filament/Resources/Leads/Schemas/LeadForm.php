<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Lead')
                    ->columns(2)
                    ->schema([
                        Select::make('source_id')
                            ->label('Sumber')
                            ->relationship('source', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('assigned_to')
                            ->label('Ditugaskan Kepada')
                            ->relationship('assignedTo', 'first_name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('first_name')
                            ->label('Nama Depan')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label('Nama Belakang')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('company_name')
                            ->label('Nama Perusahaan')
                            ->maxLength(255),
                        TextInput::make('industry')
                            ->label('Industri')
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('baru')
                            ->options([
                                'baru' => 'Baru',
                                'dihubungi' => 'Dihubungi',
                                'terkualifikasi' => 'Terkualifikasi',
                                'tidak_tertarik' => 'Tidak Tertarik',
                                'terkonversi' => 'Terkonversi',
                            ]),
                        TextInput::make('score')
                            ->label('Skor')
                            ->numeric()
                            ->default(0),
                        DateTimePicker::make('next_follow_up')
                            ->label('Follow Up Selanjutnya'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

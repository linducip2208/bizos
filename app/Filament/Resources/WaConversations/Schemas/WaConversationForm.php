<?php

namespace App\Filament\Resources\WaConversations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WaConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Percakapan WA')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('contact_phone')
                            ->label('No. Telepon')
                            ->required()
                            ->maxLength(30),
                        TextInput::make('contact_name')
                            ->label('Nama Kontak')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('unread_count')
                            ->label('Belum Dibaca')
                            ->numeric()
                            ->default(0),
                        Select::make('assigned_to')
                            ->label('Ditugaskan Kepada')
                            ->relationship('assignedTo', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('aktif')
                            ->options([
                                'aktif' => 'Aktif',
                                'tertunda' => 'Tertunda',
                                'selesai' => 'Selesai',
                                'spam' => 'Spam',
                            ]),
                        DateTimePicker::make('last_message_at')
                            ->label('Pesan Terakhir'),
                        Textarea::make('last_message')
                            ->label('Pesan Terakhir')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

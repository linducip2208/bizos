<?php

namespace App\Filament\Resources\Notifications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Notifikasi')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        TextInput::make('notification_type')
                            ->label('Tipe Notifikasi')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('body')
                            ->label('Body')
                            ->columnSpanFull(),
                        KeyValue::make('data')
                            ->label('Data Tambahan'),
                        Select::make('channel')
                            ->label('Channel')
                            ->options([
                                'email' => 'Email',
                                'sms' => 'SMS',
                                'whatsapp' => 'WhatsApp',
                                'push' => 'Push Notification',
                                'in_app' => 'In-App',
                            ])
                            ->required(),
                        Toggle::make('is_read')
                            ->label('Sudah Dibaca'),
                        DateTimePicker::make('read_at')
                            ->label('Waktu Dibaca'),
                        DateTimePicker::make('sent_at')
                            ->label('Waktu Kirim'),
                    ]),
            ]);
    }
}
<?php

namespace App\Filament\Resources\EmailLogs\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EmailLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Email')
                    ->columns(2)
                    ->schema([
                        Select::make('lead_id')
                            ->label('Lead')
                            ->relationship('lead', 'email')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('client_id')
                            ->label('Klien')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('to_email')
                            ->label('Email Tujuan')
                            ->email()
                            ->required(),
                        TextInput::make('subject')
                            ->label('Subjek')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('sent')
                            ->options([
                                'sent' => 'Terkirim',
                                'opened' => 'Dibuka',
                                'clicked' => 'Diklik',
                                'bounced' => 'Bounced',
                                'replied' => 'Dibalas',
                            ]),
                        TextInput::make('message_id')
                            ->label('Message ID')
                            ->nullable(),
                        Textarea::make('body')
                            ->label('Isi Email')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

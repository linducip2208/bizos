<?php

namespace App\Filament\Resources\EmailCampaign\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmailCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Kampanye')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Kampanye')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('subject')
                            ->label('Subjek Email')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sender_name')
                            ->label('Nama Pengirim')
                            ->required()
                            ->maxLength(255)
                            ->default(config('app.name')),
                        TextInput::make('sender_email')
                            ->label('Email Pengirim')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->default(config('mail.from.address')),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'scheduled' => 'Terjadwal',
                            ])
                            ->default('draft')
                            ->required(),
                        DateTimePicker::make('scheduled_at')
                            ->label('Jadwal Kirim')
                            ->visible(fn ($get) => $get('status') === 'scheduled'),
                    ]),
                Section::make('Konten Email')
                    ->schema([
                        RichEditor::make('template_content')
                            ->label('Template HTML')
                            ->columnSpanFull()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('email-attachments'),
                    ]),
            ]);
    }
}
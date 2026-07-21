<?php

namespace App\Filament\Resources\FormSubmissions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FormSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Submit Formulir')
                    ->columns(2)
                    ->schema([
                        Select::make('form_id')
                            ->label('Formulir')
                            ->relationship('form', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        TextInput::make('submitter_email')
                            ->label('Email Pengirim')
                            ->email()
                            ->maxLength(255),
                        Select::make('submitted_by')
                            ->label('Dikirim Oleh')
                            ->relationship('submittedBy', 'name')
                            ->nullable()
                            ->preload()
                            ->searchable(),
                        DateTimePicker::make('submitted_at')
                            ->label('Waktu Kirim')
                            ->default(now()),
                    ]),
            ]);
    }
}

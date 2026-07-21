<?php

namespace App\Filament\Resources\JournalEntry\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Entri Jurnal')
                    ->columns(2)
                    ->schema([
                        Select::make('journal_id')
                            ->label('Jurnal')
                            ->relationship('journal', 'journal_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('coa_id')
                            ->label('Akun')
                            ->relationship('coa', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('debit')
                            ->label('Debit')
                            ->numeric()
                            ->default(0),
                        TextInput::make('credit')
                            ->label('Kredit')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
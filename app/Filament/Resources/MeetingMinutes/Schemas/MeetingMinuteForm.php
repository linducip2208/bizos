<?php

namespace App\Filament\Resources\MeetingMinutes\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class MeetingMinuteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Notula Rapat')
                    ->schema([
                        Select::make('meeting_id')
                            ->label('Rapat')
                            ->relationship('meeting', 'title')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Select::make('recorded_by')
                            ->label('Dicatat Oleh')
                            ->relationship('recordedBy', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        RichEditor::make('content')
                            ->label('Isi Notula')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

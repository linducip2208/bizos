<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuizForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kuis')
                    ->columns(2)
                    ->schema([
                        Select::make('lesson_id')
                            ->label('Lesson')
                            ->relationship('lesson', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('passing_score')
                            ->label('Skor Minimum')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('time_limit_minutes')
                            ->label('Batas Waktu (menit)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_attempts')
                            ->label('Maksimal Percobaan')
                            ->numeric()
                            ->minValue(1),
                        Toggle::make('is_required')
                            ->label('Wajib')
                            ->default(false),
                    ]),
            ]);
    }
}
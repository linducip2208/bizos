<?php

namespace App\Filament\Resources\FeedbackAnswers\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeedbackAnswerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Jawaban Feedback')
                    ->columns(2)
                    ->schema([
                        Select::make('reviewer_id')
                            ->label('Reviewer')
                            ->relationship('reviewer', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('question_id')
                            ->label('Pertanyaan')
                            ->relationship('question', 'question')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('rating')
                            ->label('Rating')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(0.5)
                            ->nullable(),
                        Textarea::make('text_answer')
                            ->label('Jawaban Teks')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

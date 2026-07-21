<?php

namespace App\Filament\Resources\QuizQuestions\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class QuizQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Soal Kuis')
                    ->columns(2)
                    ->schema([
                        Select::make('quiz_id')
                            ->label('Kuis')
                            ->relationship('quiz', 'title')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Textarea::make('question')
                            ->label('Pertanyaan')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('question_type')
                            ->label('Tipe Pertanyaan')
                            ->options([
                                'multiple_choice' => 'Pilihan Ganda',
                                'true_false' => 'Benar/Salah',
                                'essay' => 'Essay',
                                'fill_in_blank' => 'Isian Singkat',
                            ])
                            ->required(),
                        KeyValue::make('options')
                            ->label('Opsi Jawaban')
                            ->helperText('Untuk pilihan ganda/benar-salah. Key: label, Value: value'),
                        TextInput::make('correct_answer')
                            ->label('Jawaban Benar')
                            ->maxLength(255),
                        TextInput::make('points')
                            ->label('Poin')
                            ->numeric()
                            ->default(1),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
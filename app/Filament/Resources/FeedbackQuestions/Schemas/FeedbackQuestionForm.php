<?php

namespace App\Filament\Resources\FeedbackQuestions\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeedbackQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Pertanyaan Feedback')
                    ->columns(2)
                    ->schema([
                        Select::make('cycle_id')
                            ->label('Siklus Feedback')
                            ->relationship('cycle', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'performance' => 'Performa',
                                'competency' => 'Kompetensi',
                                'behavior' => 'Perilaku',
                                'goal' => 'Target',
                                'development' => 'Pengembangan',
                                'general' => 'Umum',
                            ])
                            ->required(),
                        Select::make('question_type')
                            ->label('Tipe Pertanyaan')
                            ->options([
                                'rating_scale' => 'Skala Rating (1-5)',
                                'text' => 'Teks',
                                'multiple_choice' => 'Pilihan Ganda',
                                'yes_no' => 'Ya/Tidak',
                            ])
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                        Textarea::make('question')
                            ->label('Pertanyaan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
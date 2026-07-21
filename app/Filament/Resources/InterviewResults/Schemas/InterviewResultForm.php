<?php

namespace App\Filament\Resources\InterviewResults\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InterviewResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Hasil Interview')
                    ->columns(2)
                    ->schema([
                        Select::make('interview_id')
                            ->label('Interview')
                            ->relationship('interview', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('interviewer_id')
                            ->label('Pewawancara')
                            ->relationship('interviewer', 'id')
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
                        Select::make('recommendation')
                            ->label('Rekomendasi')
                            ->options([
                                'strong_hire' => 'Sangat Direkomendasikan',
                                'hire' => 'Direkomendasikan',
                                'consider' => 'Dipertimbangkan',
                                'not_recommended' => 'Tidak Direkomendasikan',
                            ])
                            ->nullable(),
                        Textarea::make('comments')
                            ->label('Komentar')
                            ->rows(4)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
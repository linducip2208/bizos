<?php

namespace App\Filament\Resources\CourseLessons\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseLessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pelajaran')
                    ->columns(2)
                    ->schema([
                        Select::make('module_id')
                            ->label('Modul')
                            ->relationship('module', 'title')
                            ->required()
                            ->preload()
                            ->searchable(),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Select::make('content_type')
                            ->label('Tipe Konten')
                            ->options([
                                'video' => 'Video',
                                'article' => 'Artikel',
                                'pdf' => 'PDF',
                                'quiz' => 'Kuis',
                                'assignment' => 'Tugas',
                                'external_link' => 'Link Eksternal',
                            ])
                            ->required(),
                        RichEditor::make('content')
                            ->label('Konten')
                            ->columnSpanFull(),
                        FileUpload::make('file_path')
                            ->label('File')
                            ->directory('course-lessons'),
                        TextInput::make('external_url')
                            ->label('URL Eksternal')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('duration_minutes')
                            ->label('Durasi (menit)')
                            ->numeric(),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_preview')
                            ->label('Preview'),
                    ]),
            ]);
    }
}

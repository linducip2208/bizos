<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kursus')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('category')
                            ->label('Kategori')
                            ->maxLength(255),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                        FileUpload::make('cover_image')
                            ->label('Gambar Cover')
                            ->image()
                            ->directory('courses/covers')
                            ->imagePreviewHeight('200')
                            ->maxSize(2048),
                        TextInput::make('duration_minutes')
                            ->label('Durasi (menit)')
                            ->numeric()
                            ->minValue(0),
                        Toggle::make('is_published')
                            ->label('Dipublikasikan')
                            ->default(false),
                        DatePicker::make('enrollment_start')
                            ->label('Pendaftaran Mulai'),
                        DatePicker::make('enrollment_end')
                            ->label('Pendaftaran Selesai'),
                    ]),
            ]);
    }
}
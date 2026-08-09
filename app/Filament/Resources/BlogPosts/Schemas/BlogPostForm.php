<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Konten Post')->columns(2)->schema([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('author_id')
                    ->label('Penulis')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->nullable()
                    ->columnSpanFull()
                    ->rows(3),
                RichEditor::make('content')
                    ->label('Konten')
                    ->required()
                    ->columnSpanFull(),
            ]),
            Section::make('Gambar & Publikasi')->columns(2)->schema([
                FileUpload::make('featured_image')
                    ->label('Gambar Utama')
                    ->image()
                    ->directory('blog')
                    ->nullable(),
                Toggle::make('is_published')
                    ->label('Publikasikan')
                    ->default(false),
                DateTimePicker::make('published_at')
                    ->label('Tanggal Publikasi')
                    ->nullable()
                    ->default(now()),
            ]),
            Section::make('SEO Meta')->columns(2)->schema([
                TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->nullable()
                    ->maxLength(255),
                Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->nullable()
                    ->columnSpanFull(),
            ]),
        ]);
    }
}

<?php

namespace App\Filament\Resources\WikiPageResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class WikiPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Halaman Wiki')->columns(2)->schema([
                Select::make('company_id')->label('Perusahaan')->relationship('company','name')->searchable()->preload()->required(),
                Select::make('category_id')->label('Kategori')->relationship('category','name')->searchable()->preload(),
                TextInput::make('title')->label('Judul')->required()->maxLength(255)->columnSpanFull(),
                TextInput::make('slug')->label('Slug')->required()->maxLength(255),
                Select::make('status')->label('Status')->options(['draft'=>'Draft','published'=>'Publikasi','archived'=>'Arsip'])->default('draft')->required(),
                Select::make('author_id')->label('Penulis')->relationship('author','name')->searchable()->preload(),
                DateTimePicker::make('published_at')->label('Tanggal Publikasi'),
                RichEditor::make('content')->label('Konten')->required()->columnSpanFull(),
            ]),
        ]);
    }
}

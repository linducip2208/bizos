<?php

namespace App\Filament\Resources\BlogCategories\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BlogCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kategori Blog')->columns(2)->schema([
                TextInput::make('name')->label('Nama')->required()->maxLength(255),
                TextInput::make('slug')->label('Slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                Textarea::make('description')->label('Deskripsi')->nullable()->columnSpanFull(),
            ]),
        ]);
    }
}

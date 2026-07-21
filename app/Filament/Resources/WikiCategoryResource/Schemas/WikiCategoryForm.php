<?php

namespace App\Filament\Resources\WikiCategoryResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WikiCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kategori Wiki')->columns(2)->schema([
                Select::make('company_id')->label('Perusahaan')->relationship('company','name')->searchable()->preload()->required(),
                TextInput::make('name')->label('Nama')->required()->maxLength(255),
                TextInput::make('slug')->label('Slug')->required()->maxLength(255),
                Select::make('parent_id')->label('Induk')->relationship('parent','name')->searchable()->preload(),
                TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            ]),
        ]);
    }
}

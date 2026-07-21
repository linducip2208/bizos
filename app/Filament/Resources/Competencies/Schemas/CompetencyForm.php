<?php

namespace App\Filament\Resources\Competencies\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CompetencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kompetensi')->columns(2)->schema([
                Select::make('company_id')->label('Perusahaan')->relationship('company','name')->searchable()->preload()->required(),
                TextInput::make('name')->label('Nama Kompetensi')->required()->maxLength(255),
                TextInput::make('category')->label('Kategori')->maxLength(100)->datalist(['Teknis','Soft Skill','Manajerial','Leadership','Fungsional']),
                Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
                Textarea::make('proficiency_levels')->label('Level Kemahiran (JSON)')->helperText('Format JSON: ["Pemula","Dasar","Menengah","Mahir","Ahli"]')->columnSpanFull(),
            ]),
        ]);
    }
}
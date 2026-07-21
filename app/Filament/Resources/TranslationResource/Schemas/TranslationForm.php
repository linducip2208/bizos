<?php

namespace App\Filament\Resources\TranslationResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TranslationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Terjemahan')->columns(1)->schema([
                TextInput::make('key')->label('Key')->required()->maxLength(255)->unique(ignoreRecord: true),
                Select::make('locale')->label('Bahasa')->options(['id'=>'Bahasa Indonesia','en'=>'English'])->default('id')->required(),
                Textarea::make('value')->label('Nilai')->required()->rows(3),
            ]),
        ]);
    }
}

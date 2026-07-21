<?php

namespace App\Filament\Resources\FormFields\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FormFieldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Field Formulir')
                    ->columns(2)
                    ->schema([
                        Select::make('form_id')
                            ->label('Formulir')
                            ->relationship('form', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        TextInput::make('label')
                            ->label('Label')
                            ->required()
                            ->maxLength(255),
                        Select::make('field_type')
                            ->label('Tipe Field')
                            ->options([
                                'text' => 'Text',
                                'textarea' => 'Textarea',
                                'number' => 'Number',
                                'email' => 'Email',
                                'select' => 'Select',
                                'checkbox' => 'Checkbox',
                                'radio' => 'Radio',
                                'date' => 'Date',
                                'file' => 'File Upload',
                            ])
                            ->required(),
                        TextInput::make('placeholder')
                            ->label('Placeholder')
                            ->maxLength(255),
                        KeyValue::make('options')
                            ->label('Opsi (Key-Value)')
                            ->helperText('Untuk field type Select/Radio/Checkbox'),
                        Toggle::make('is_required')
                            ->label('Wajib Diisi')
                            ->default(false),
                        TextInput::make('validation_rules')
                            ->label('Aturan Validasi')
                            ->maxLength(255)
                            ->helperText('Contoh: required|min:3|max:100'),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}

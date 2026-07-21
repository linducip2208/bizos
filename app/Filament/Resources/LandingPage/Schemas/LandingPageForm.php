<?php

namespace App\Filament\Resources\LandingPage\Schemas;

use App\Models\Form;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LandingPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Halaman')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique('landing_pages', 'slug', ignoreRecord: true),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->required(),
                        Select::make('form_id')
                            ->label('Form (dari Kolaborasi)')
                            ->options(
                                Form::where('company_id', auth()->user()->company_id)
                                    ->pluck('name', 'id')
                            )
                            ->nullable()
                            ->searchable()
                            ->preload(),
                    ]),
                Section::make('SEO')
                    ->columns(1)
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->maxLength(320),
                    ]),
                Section::make('Konten Page Builder')
                    ->schema([
                        KeyValue::make('content')
                            ->label('Blok Konten (JSON)')
                            ->keyLabel('Section')
                            ->valueLabel('Content')
                            ->columnSpanFull()
                            ->helperText('Gunakan key-value untuk menyusun blok konten halaman.'),
                    ]),
            ]);
    }
}
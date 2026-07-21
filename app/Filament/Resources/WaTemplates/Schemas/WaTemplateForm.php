<?php

namespace App\Filament\Resources\WaTemplates\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WaTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Template')
                    ->columns(1)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Template')
                            ->required()
                            ->maxLength(255),
                        Select::make('category')
                            ->label('Kategori')
                            ->required()
                            ->default('marketing')
                            ->options([
                                'marketing' => 'Marketing',
                                'transaksional' => 'Transaksional',
                                'layanan' => 'Layanan',
                                'pengingat' => 'Pengingat',
                            ]),
                        Select::make('language')
                            ->label('Bahasa')
                            ->required()
                            ->default('id')
                            ->options([
                                'id' => 'Indonesia',
                                'en' => 'Inggris',
                            ]),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('draft')
                            ->options([
                                'draft' => 'Draft',
                                'aktif' => 'Aktif',
                                'ditolak' => 'Ditolak',
                            ]),
                        Textarea::make('content')
                            ->label('Isi Pesan')
                            ->rows(6)
                            ->required()
                            ->helperText('Gunakan {nama} untuk placeholder nama penerima'),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\WaBlastLogs\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WaBlastLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Log Blast WA')
                    ->columns(2)
                    ->schema([
                        Select::make('campaign_id')
                            ->label('Kampanye')
                            ->relationship('campaign', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('contact_phone')
                            ->label('No. Telepon')
                            ->required()
                            ->maxLength(30),
                        TextInput::make('contact_name')
                            ->label('Nama Kontak')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('tertunda')
                            ->options([
                                'tertunda' => 'Tertunda',
                                'terkirim' => 'Terkirim',
                                'terkirim_wa' => 'Terkirim WA',
                                'dibaca' => 'Dibaca',
                                'gagal' => 'Gagal',
                            ]),
                        Textarea::make('message')
                            ->label('Pesan')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

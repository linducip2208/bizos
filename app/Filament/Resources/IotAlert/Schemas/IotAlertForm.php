<?php

namespace App\Filament\Resources\IotAlert\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class IotAlertForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Status Alert')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'acknowledged' => 'Diakui',
                                'resolved' => 'Terselesaikan',
                            ])
                            ->required(),
                        Textarea::make('message')
                            ->label('Pesan')
                            ->columnSpanFull()
                            ->disabled(),
                    ]),
            ]);
    }
}
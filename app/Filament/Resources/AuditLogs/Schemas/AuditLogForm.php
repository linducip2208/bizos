<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Audit Log')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->disabled(),
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->disabled(),
                        TextInput::make('action')
                            ->label('Aksi')
                            ->disabled(),
                        TextInput::make('entity_type')
                            ->label('Tipe Entitas')
                            ->disabled(),
                        TextInput::make('entity_id')
                            ->label('ID Entitas')
                            ->disabled(),
                        KeyValue::make('old_values')
                            ->label('Nilai Lama')
                            ->disabled(),
                        KeyValue::make('new_values')
                            ->label('Nilai Baru')
                            ->disabled(),
                        TextInput::make('ip_address')
                            ->label('IP Address')
                            ->disabled(),
                        TextInput::make('user_agent')
                            ->label('User Agent')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

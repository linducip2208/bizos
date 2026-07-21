<?php

namespace App\Filament\Resources\SystemSettings\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SystemSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengaturan')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('value')
                            ->label('Value')
                            ->rows(4)
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'string' => 'String',
                                'integer' => 'Integer',
                                'boolean' => 'Boolean',
                                'json' => 'JSON',
                                'array' => 'Array',
                            ])
                            ->default('string')
                            ->required(),
                        Select::make('group')
                            ->label('Group')
                            ->options([
                                'general' => 'General',
                                'email' => 'Email',
                                'notification' => 'Notifikasi',
                                'security' => 'Keamanan',
                                'billing' => 'Billing',
                            ])
                            ->required(),
                    ]),
            ]);
    }
}

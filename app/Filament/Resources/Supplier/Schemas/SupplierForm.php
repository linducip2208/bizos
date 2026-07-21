<?php

namespace App\Filament\Resources\Supplier\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Supplier')
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(200),
                        TextInput::make('contact_person')
                            ->label('Kontak Person')
                            ->maxLength(200),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->maxLength(20),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(200),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Informasi Pajak & Pembayaran')
                    ->schema([
                        TextInput::make('tax_number')
                            ->label('NPWP')
                            ->maxLength(50),
                        Select::make('payment_terms')
                            ->label('Syarat Pembayaran')
                            ->options([
                                'COD' => 'COD',
                                'CBD' => 'CBD',
                                'NET7' => 'NET 7',
                                'NET15' => 'NET 15',
                                'NET30' => 'NET 30',
                                'NET60' => 'NET 60',
                                'NET90' => 'NET 90',
                            ])
                            ->default('NET30'),
                    ])->columns(2),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}

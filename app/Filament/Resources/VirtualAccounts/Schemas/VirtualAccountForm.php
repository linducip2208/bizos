<?php

namespace App\Filament\Resources\VirtualAccounts\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VirtualAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Virtual Account')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('bank_account_id')
                            ->label('Rekening Bank')
                            ->relationship('bankAccount', 'account_number')
                            ->getOptionLabelFromRecordUsing(fn($r) => "{$r->bank_name} - {$r->account_number} ({$r->account_name})")
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('bank')
                            ->label('Bank')
                            ->options([
                                'bca' => 'BCA',
                                'mandiri' => 'Mandiri',
                                'bri' => 'BRI',
                                'bni' => 'BNI',
                                'cimb' => 'CIMB Niaga',
                            ])
                            ->required(),
                        TextInput::make('va_number')
                            ->label('Nomor VA')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('name')
                            ->label('Nama Pemilik')
                            ->required()
                            ->maxLength(200),
                        TextInput::make('expected_amount')
                            ->label('Jumlah Diharapkan (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                        TextInput::make('paid_amount')
                            ->label('Jumlah Dibayar (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu',
                                'active' => 'Aktif',
                                'paid' => 'Sudah Dibayar',
                                'expired' => 'Kadaluarsa',
                                'closed' => 'Ditutup',
                            ])
                            ->default('pending')
                            ->required(),
                        DateTimePicker::make('expiry_at')
                            ->label('Kadaluarsa')
                            ->nullable(),
                    ]),
            ]);
    }
}

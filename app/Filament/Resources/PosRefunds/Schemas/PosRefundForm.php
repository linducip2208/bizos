<?php

namespace App\Filament\Resources\PosRefunds\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PosRefundForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Refund POS')
                    ->columns(2)
                    ->schema([
                        Select::make('transaction_id')
                            ->label('Transaksi')
                            ->relationship('transaction', 'receipt_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('refund_number')
                            ->label('No. Refund')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        DateTimePicker::make('refund_date')
                            ->label('Tanggal Refund')
                            ->required()
                            ->default(now()),
                        Select::make('refunded_by')
                            ->label('Direfund Oleh')
                            ->relationship('refundedBy', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approvedBy', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Textarea::make('reason')
                            ->label('Alasan')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

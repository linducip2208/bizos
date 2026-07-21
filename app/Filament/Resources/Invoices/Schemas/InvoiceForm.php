<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Faktur')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('invoice_number')
                            ->label('Nomor Faktur')
                            ->required()
                            ->maxLength(100),
                        Select::make('invoice_type')
                            ->label('Tipe Faktur')
                            ->options([
                                'sales' => 'Penjualan',
                                'purchase' => 'Pembelian',
                                'service' => 'Jasa',
                                'other' => 'Lainnya',
                            ])
                            ->required(),
                        DatePicker::make('invoice_date')
                            ->label('Tanggal Faktur')
                            ->required(),
                        DatePicker::make('due_date')
                            ->label('Jatuh Tempo')
                            ->required(),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->required()
                            ->prefix('Rp'),
                        TextInput::make('discount_amount')
                            ->label('Diskon')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp'),
                        TextInput::make('tax_amount')
                            ->label('Pajak')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp'),
                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->required()
                            ->prefix('Rp'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Terkirim',
                                'partial' => 'Sebagian',
                                'paid' => 'Lunas',
                                'overdue' => 'Terlambat',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('draft')
                            ->required(),
                    ]),
            ]);
    }
}
<?php

namespace App\Filament\Resources\RecurringInvoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RecurringInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Invoice Berulang')
                    ->columns(3)
                    ->schema([
                        Select::make('client_id')
                            ->label('Klien')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                        Select::make('frequency')
                            ->label('Frekuensi')
                            ->options([
                                'daily' => 'Harian',
                                'weekly' => 'Mingguan',
                                'monthly' => 'Bulanan',
                                'quarterly' => 'Kuartalan',
                                'yearly' => 'Tahunan',
                            ])
                            ->default('monthly')
                            ->required(),
                        TextInput::make('interval')
                            ->label('Interval')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->helperText('Setiap berapa periode (misal: 1 = tiap bulan).')
                            ->required(),
                        TextInput::make('amount')
                            ->label('Nominal')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('Rp'),
                        TextInput::make('tax_percent')
                            ->label('Pajak %')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('%')
                            ->nullable(),
                        Select::make('currency_id')
                            ->label('Mata Uang')
                            ->relationship('currency', 'code')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('invoice_template_id')
                            ->label('Template Invoice')
                            ->relationship('invoiceTemplate', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
                Section::make('Item Baris')
                    ->columns(1)
                    ->schema([
                        Repeater::make('items')
                            ->label('Item')
                            ->columns(4)
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Item')
                            ->default([])
                            ->schema([
                                TextInput::make('description')
                                    ->label('Deskripsi')
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(1)
                                    ->required(),
                                TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->required(),
                                TextInput::make('tax_rate')
                                    ->label('Pajak %')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->suffix('%'),
                            ]),
                    ]),
                Section::make('Jadwal & Pengaturan')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->default(now())
                            ->required(),
                        DatePicker::make('next_run_date')
                            ->label('Jadwal Berikutnya')
                            ->default(now())
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Tanggal Berakhir')
                            ->nullable(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'paused' => 'Ditunda',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('active')
                            ->required(),
                        Toggle::make('auto_send')
                            ->label('Kirim Otomatis')
                            ->default(false),
                    ]),
            ]);
    }
}

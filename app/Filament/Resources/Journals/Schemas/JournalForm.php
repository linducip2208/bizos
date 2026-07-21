<?php

namespace App\Filament\Resources\Journals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class JournalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Jurnal')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('journal_number')
                            ->label('Nomor Jurnal')
                            ->required()
                            ->maxLength(100),
                        DatePicker::make('journal_date')
                            ->label('Tanggal Jurnal')
                            ->required(),
                        Select::make('journal_type')
                            ->label('Tipe Jurnal')
                            ->options([
                                'general' => 'Umum',
                                'sales' => 'Penjualan',
                                'purchase' => 'Pembelian',
                                'cash_receipt' => 'Penerimaan Kas',
                                'cash_payment' => 'Pengeluaran Kas',
                                'adjustment' => 'Penyesuaian',
                                'opening' => 'Saldo Awal',
                                'closing' => 'Tutup Buku',
                                'depreciation' => 'Penyusutan',
                            ])
                            ->required(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'posted' => 'Posted',
                                'void' => 'Void',
                            ])
                            ->default('draft')
                            ->required(),
                    ]),
            ]);
    }
}

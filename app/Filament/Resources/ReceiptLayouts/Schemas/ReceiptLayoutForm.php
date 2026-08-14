<?php

namespace App\Filament\Resources\ReceiptLayouts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReceiptLayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Layout')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Layout')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('cth: Struk Standar 58mm'),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'pos_receipt' => 'Struk POS',
                                'invoice' => 'Invoice',
                                'label' => 'Label',
                            ])
                            ->default('pos_receipt')
                            ->required(),
                        Select::make('font_size')
                            ->label('Ukuran Font')
                            ->options([
                                'small' => 'Kecil',
                                'medium' => 'Sedang',
                                'large' => 'Besar',
                            ])
                            ->default('medium')
                            ->required(),
                    ]),
                Section::make('Teks Struk')
                    ->columns(2)
                    ->schema([
                        Textarea::make('header_text')
                            ->label('Teks Header')
                            ->rows(3)
                            ->placeholder('cth: Selamat datang di toko kami!')
                            ->helperText('Ditampilkan di bagian atas setelah nama toko.'),
                        Textarea::make('footer_text')
                            ->label('Teks Footer')
                            ->rows(3)
                            ->placeholder('cth: Barang yang sudah dibeli tidak dapat ditukar.')
                            ->helperText('Ditampilkan di bagian bawah sebelum potongan kertas.'),
                    ]),
                Section::make('Tampilan')
                    ->columns(3)
                    ->schema([
                        Toggle::make('show_logo')
                            ->label('Tampilkan Logo')
                            ->default(true),
                        Toggle::make('show_qr')
                            ->label('Tampilkan QR Code')
                            ->default(false),
                        Toggle::make('show_tax_summary')
                            ->label('Tampilkan Ringkasan Pajak')
                            ->default(true),
                        Toggle::make('show_payment_summary')
                            ->label('Tampilkan Ringkasan Pembayaran')
                            ->default(true),
                        Toggle::make('is_default')
                            ->label('Jadikan Layout Utama')
                            ->helperText('Satu layout utama per tipe.'),
                    ]),
            ]);
    }
}

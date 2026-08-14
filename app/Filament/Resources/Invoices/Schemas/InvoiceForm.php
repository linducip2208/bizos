<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Invoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
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
                        Select::make('currency_id')
                            ->label('Mata Uang')
                            ->relationship('currency', 'code')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('exchange_rate')
                            ->label('Kurs')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
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
                Section::make('Link Pembayaran')
                    ->description('Bagikan link publik agar pelanggan dapat melihat dan membayar faktur tanpa login.')
                    ->columns(1)
                    ->visible(fn (?Invoice $record) => $record !== null)
                    ->schema([
                        Placeholder::make('payment_link_url')
                            ->label('Link Pembayaran')
                            ->content(function (?Invoice $record) {
                                if (! $record?->payment_token) {
                                    return new HtmlString(
                                        '<div class="text-sm text-gray-500">Belum ada link. Gunakan aksi <strong>Salin Link Pembayaran</strong> pada daftar faktur untuk membuat link.</div>'
                                    );
                                }

                                $url = $record->getPaymentLinkUrl();
                                $expired = $record->isPaymentLinkExpired();
                                $badge = $expired
                                    ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-danger-50 text-danger-700">Kedaluwarsa</span>'
                                    : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-success-50 text-success-700">Aktif</span>';

                                return new HtmlString(
                                    '<div class="flex flex-wrap items-center gap-3">'
                                    .'<code class="text-sm break-all bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">'.e($url).'</code>'
                                    .'<button type="button" onclick="navigator.clipboard.writeText(\''.e($url).'\');this.textContent=\'Tersalin!\';setTimeout(()=>this.textContent=\'Salin\',1500)" class="fi-btn fi-btn-size-md fi-btn-color-primary fi-btn-style-solid">Salin</button>'
                                    .$badge
                                    .'</div>'
                                    .'<div class="text-xs text-gray-400 mt-2">Berlaku sampai '.$record->payment_link_expires_at?->format('d M Y H:i').'</div>'
                                );
                            }),
                    ]),
            ]);
    }
}

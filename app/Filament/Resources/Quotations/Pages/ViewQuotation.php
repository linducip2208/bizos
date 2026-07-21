<?php

namespace App\Filament\Resources\Quotations\Pages;

use App\Filament\Resources\Quotations\QuotationResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\RepeatableEntry;
use Filament\Schemas\Components\Section as InfoSection;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class ViewQuotation extends ViewRecord
{
    protected static string $resource = QuotationResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                InfoSection::make('Informasi Quotation')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('quotation_number')->label('No. Quotation'),
                        TextEntry::make('client.name')->label('Klien'),
                        TextEntry::make('contact.first_name')->label('Kontak'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'draft' => 'gray', 'sent' => 'info', 'accepted' => 'success',
                                'rejected' => 'danger', 'expired' => 'warning', 'converted' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('valid_until')->label('Berlaku Sampai')->date('d M Y'),
                        TextEntry::make('total')->label('Total')->money('IDR'),
                        TextEntry::make('notes')->label('Catatan')->columnSpanFull(),
                        TextEntry::make('terms')->label('Syarat & Ketentuan')->columnSpanFull(),
                    ]),
                InfoSection::make('Item')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')->label('Produk'),
                                TextEntry::make('description')->label('Deskripsi'),
                                TextEntry::make('quantity')->label('Qty'),
                                TextEntry::make('unit_price')->label('Harga')->money('IDR'),
                                TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                            ])
                            ->columns(5),
                    ]),
            ]);
    }
}

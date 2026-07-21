<?php

namespace App\Filament\Resources\SalesInvoices\Pages;

use App\Filament\Resources\SalesInvoices\SalesInvoiceResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section as InfoSection;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class ViewSalesInvoice extends ViewRecord
{
    protected static string $resource = SalesInvoiceResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                InfoSection::make('Informasi Invoice')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('invoice_number')->label('No. Invoice'),
                        TextEntry::make('client.name')->label('Klien'),
                        TextEntry::make('salesOrder.so_number')->label('Sales Order'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'draft' => 'gray', 'sent' => 'info', 'partial' => 'warning',
                                'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('due_date')->label('Jatuh Tempo')->date('d M Y'),
                        TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                        TextEntry::make('tax')->label('Pajak')->money('IDR'),
                        TextEntry::make('total')->label('Total')->money('IDR'),
                        TextEntry::make('paid_amount')->label('Dibayar')->money('IDR'),
                    ]),
            ]);
    }
}

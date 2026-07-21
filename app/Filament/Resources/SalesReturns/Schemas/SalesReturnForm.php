<?php

namespace App\Filament\Resources\SalesReturns\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SalesReturnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Return')
                    ->columns(2)
                    ->schema([
                        Select::make('client_id')
                            ->label('Klien')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('sales_invoice_id')
                            ->label('Invoice')
                            ->relationship('salesInvoice', 'invoice_number')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('draft')
                            ->options([
                                'draft' => 'Draft',
                                'received' => 'Diterima',
                                'refunded' => 'Direfund',
                            ]),
                        TextInput::make('total')
                            ->label('Total Return')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Textarea::make('reason')
                            ->label('Alasan Return')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

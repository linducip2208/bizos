<?php

namespace App\Filament\Resources\GuestFolios\Schemas;

use App\Models\RoomBooking;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GuestFolioForm
{
    public static function configure(Schema $schema): Schema
    {
        $companyId = auth()->user()->company_id;

        return $schema
            ->components([
                Section::make('Informasi Folio')
                    ->schema([
                        Select::make('booking_id')
                            ->label('Booking')
                            ->options(
                                RoomBooking::where('company_id', $companyId)
                                    ->with('room')
                                    ->get()
                                    ->mapWithKeys(fn ($b) => [
                                        $b->id => "{$b->guest_name} - {$b->room->room_number} ({$b->check_in_date->format('d M')} - {$b->check_out_date->format('d M')})"
                                    ])
                            )
                            ->searchable()
                            ->required(),
                        TextInput::make('folio_number')
                            ->label('Nomor Folio')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                    ])->columns(2),

                Section::make('Ringkasan Biaya')
                    ->schema([
                        TextInput::make('total_room_charges')
                            ->label('Biaya Kamar (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('total_service_charges')
                            ->label('Biaya Layanan (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('total_tax')
                            ->label('Pajak (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('grand_total')
                            ->label('Grand Total (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                    ])->columns(2),

                Section::make('Pembayaran')
                    ->schema([
                        TextInput::make('deposit_paid')
                            ->label('Deposit Dibayar (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('balance_due')
                            ->label('Sisa Tagihan (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'pending' => 'Pending',
                                'partially_paid' => 'Dibayar Sebagian',
                                'paid' => 'Lunas',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(3),
            ]);
    }
}
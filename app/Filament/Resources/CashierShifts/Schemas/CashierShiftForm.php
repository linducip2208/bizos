<?php

namespace App\Filament\Resources\CashierShifts\Schemas;

use App\Models\CashDenomination;
use App\Services\CashDenominationService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CashierShiftForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Shift')
                    ->columns(3)
                    ->schema([
                        Select::make('employee_id')
                            ->label('Kasir')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('shift_date')
                            ->label('Tanggal Shift')
                            ->required()
                            ->default(now()),
                        DateTimePicker::make('opening_time')
                            ->label('Waktu Buka')
                            ->required(),
                        TextInput::make('opening_balance')
                            ->label('Saldo Awal')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                    ]),
                Section::make('Penutupan Shift')
                    ->columns(3)
                    ->schema([
                        DateTimePicker::make('closing_time')
                            ->label('Waktu Tutup'),
                        TextInput::make('closing_balance')
                            ->label('Saldo Akhir')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('expected_cash')
                            ->label('Kas Diharapkan')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('actual_cash')
                            ->label('Kas Aktual')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('difference')
                            ->label('Selisih')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('total_transactions')
                            ->label('Total Transaksi')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        TextInput::make('total_sales')
                            ->label('Total Penjualan')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Buka',
                                'closed' => 'Tutup',
                            ])
                            ->default('open')
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Perhitungan Pecahan Uang (Rekonsiliasi Kas)')
                    ->description('Input jumlah lembar/keping per pecahan untuk mencocokkan kas aktual saat tutup shift.')
                    ->columns(1)
                    ->schema([
                        Repeater::make('shiftDenominations')
                            ->label('Pecahan Uang')
                            ->relationship('shiftDenominations')
                            ->addActionLabel('Tambah Pecahan')
                            ->columns(3)
                            ->schema([
                                Select::make('denomination_id')
                                    ->label('Pecahan')
                                    ->options(function () {
                                        $companyId = session('current_company_id') ?? auth()->user()?->company_id;

                                        return CashDenomination::query()
                                            ->where('is_active', true)
                                            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                                            ->orderBy('sort_order')
                                            ->pluck('label', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live(),
                                TextInput::make('count')
                                    ->label('Jumlah Lembar/Keping')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->live(),
                                Placeholder::make('subtotal_label')
                                    ->label('Subtotal')
                                    ->content(function (callable $get) {
                                        $denomination = CashDenomination::find($get('denomination_id'));
                                        $count = (int) $get('count');

                                        return 'Rp ' . number_format($count * (float) ($denomination?->value ?? 0), 0, ',', '.');
                                    }),
                            ]),
                        Placeholder::make('cash_expected_placeholder')
                            ->label('Kas Diharapkan (penjualan tunai)')
                            ->content(function ($record) {
                                $expected = app(CashDenominationService::class)->calculateExpectedCash($record?->id ?? 0);

                                return 'Rp ' . number_format($expected, 0, ',', '.');
                            }),
                        Placeholder::make('cash_actual_placeholder')
                            ->label('Kas Aktual (dari pecahan)')
                            ->content(function (callable $get) {
                                $actual = self::sumRepeaterCounts($get);

                                return 'Rp ' . number_format($actual, 0, ',', '.');
                            }),
                        Placeholder::make('cash_difference_placeholder')
                            ->label('Selisih')
                            ->content(function (callable $get, $record) {
                                $actual = self::sumRepeaterCounts($get);
                                $expected = app(CashDenominationService::class)->calculateExpectedCash($record?->id ?? 0);

                                return 'Rp ' . number_format($actual - $expected, 0, ',', '.');
                            }),
                    ]),
            ]);
    }

    protected static function sumRepeaterCounts(callable $get): float
    {
        $total = 0.0;

        foreach ((array) $get('shiftDenominations') as $item) {
            $denomination = CashDenomination::find($item['denomination_id'] ?? null);
            $total += (int) ($item['count'] ?? 0) * (float) ($denomination?->value ?? 0);
        }

        return $total;
    }
}

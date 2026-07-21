<?php

namespace App\Filament\Resources\ApiKeys\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ApiKeyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kunci API')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('cth: Integrasi Mobile App'),
                        TextInput::make('rate_limit')
                            ->label('Rate Limit (req/menit)')
                            ->numeric()
                            ->default(60)
                            ->minValue(1)
                            ->maxValue(1000)
                            ->required(),
                        DateTimePicker::make('expires_at')
                            ->label('Kedaluwarsa')
                            ->nullable()
                            ->helperText('Kosongkan jika tidak ada kedaluwarsa'),
                    ]),
                Section::make('Izin Akses')
                    ->description('Pilih resource dan aksi yang diizinkan untuk kunci API ini')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('Permissions')
                            ->options([
                                'employees.read' => 'Karyawan — Read',
                                'employees.write' => 'Karyawan — Write',
                                'attendances.read' => 'Absensi — Read',
                                'attendances.write' => 'Absensi — Write',
                                'leaves.read' => 'Cuti — Read',
                                'leaves.write' => 'Cuti — Write',
                                'invoices.read' => 'Invoice — Read',
                                'invoices.write' => 'Invoice — Write',
                                'payments.read' => 'Pembayaran — Read',
                                'payments.write' => 'Pembayaran — Write',
                                'journals.read' => 'Jurnal — Read',
                                'journals.write' => 'Jurnal — Write',
                                'clients.read' => 'Klien — Read',
                                'clients.write' => 'Klien — Write',
                                'leads.read' => 'Lead — Read',
                                'leads.write' => 'Lead — Write',
                                'deals.read' => 'Deal — Read',
                                'deals.write' => 'Deal — Write',
                                'products.read' => 'Produk — Read',
                                'products.write' => 'Produk — Write',
                                'pos-transactions.read' => 'Transaksi POS — Read',
                                'pos-transactions.write' => 'Transaksi POS — Write',
                                'projects.read' => 'Proyek — Read',
                                'projects.write' => 'Proyek — Write',
                                'tasks.read' => 'Tugas — Read',
                                'tasks.write' => 'Tugas — Write',
                                'timesheets.read' => 'Timesheet — Read',
                                'timesheets.write' => 'Timesheet — Write',
                                'tickets.read' => 'Tiket — Read',
                                'tickets.write' => 'Tiket — Write',
                                'payrolls.read' => 'Payroll — Read',
                                'payrolls.write' => 'Payroll — Write',
                                '*' => 'Semua Akses (Full Access)',
                            ])
                            ->columns(3)
                            ->helperText('Pilih "*" untuk memberikan akses penuh ke semua resource'),
                    ]),
            ]);
    }
}

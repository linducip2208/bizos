<?php

namespace App\Filament\Resources\Payroll\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayrollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penggajian')
                    ->columns(2)
                    ->schema([
                        Select::make('period_id')
                            ->label('Periode')
                            ->relationship('period', 'period_code')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('gross_salary')
                            ->label('Gaji Kotor')
                            ->numeric()
                            ->required(),
                        TextInput::make('total_income_components')
                            ->label('Total Komponen Pendapatan')
                            ->numeric()
                            ->default(0),
                        TextInput::make('total_deduction_components')
                            ->label('Total Komponen Potongan')
                            ->numeric()
                            ->default(0),
                        TextInput::make('pph21_amount')
                            ->label('PPh 21')
                            ->numeric()
                            ->default(0),
                        TextInput::make('bpjs_tk_jht')
                            ->label('BPJS TK JHT')
                            ->numeric()
                            ->default(0),
                        TextInput::make('bpjs_tk_jp')
                            ->label('BPJS TK JP')
                            ->numeric()
                            ->default(0),
                        TextInput::make('bpjs_tk_jkk')
                            ->label('BPJS TK JKK')
                            ->numeric()
                            ->default(0),
                        TextInput::make('bpjs_tk_jkm')
                            ->label('BPJS TK JKM')
                            ->numeric()
                            ->default(0),
                        TextInput::make('bpjs_kes')
                            ->label('BPJS Kesehatan')
                            ->numeric()
                            ->default(0),
                        TextInput::make('net_salary')
                            ->label('Gaji Bersih')
                            ->numeric()
                            ->required(),
                        TextInput::make('attendance_days')
                            ->label('Hari Hadir')
                            ->numeric()
                            ->default(0),
                        TextInput::make('leave_days')
                            ->label('Hari Cuti')
                            ->numeric()
                            ->default(0),
                        TextInput::make('overtime_hours')
                            ->label('Jam Lembur')
                            ->numeric()
                            ->default(0),
                        TextInput::make('overtime_pay')
                            ->label('Upah Lembur')
                            ->numeric()
                            ->default(0),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'calculated' => 'Dihitung',
                                'approved' => 'Disetujui',
                                'paid' => 'Dibayar',
                            ])
                            ->required()
                            ->default('draft'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
<?php

namespace App\Filament\Resources\PaySlip\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaySlipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Slip Gaji')
                    ->columns(2)
                    ->schema([
                        Select::make('payroll_id')
                            ->label('Penggajian')
                            ->relationship('payroll', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('slip_number')
                            ->label('Nomor Slip')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('file_path')
                            ->label('File Slip')
                            ->directory('payslips')
                            ->acceptedFileTypes(['application/pdf'])
                            ->nullable(),
                        DateTimePicker::make('sent_at')
                            ->label('Terkirim Pada')
                            ->nullable(),
                        DateTimePicker::make('viewed_at')
                            ->label('Dilihat Pada')
                            ->nullable(),
                    ]),
            ]);
    }
}
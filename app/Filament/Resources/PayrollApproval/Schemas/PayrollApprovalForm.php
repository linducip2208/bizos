<?php

namespace App\Filament\Resources\PayrollApproval\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PayrollApprovalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Persetujuan Gaji')
                    ->columns(2)
                    ->schema([
                        Select::make('payroll_period_id')
                            ->label('Periode Gaji')
                            ->relationship('payrollPeriod', 'period_code')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('approver_id')
                            ->label('Approver')
                            ->relationship('approver', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('level')
                            ->label('Level')
                            ->numeric()
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->required(),
                        Textarea::make('comment')
                            ->label('Komentar')
                            ->rows(3)
                            ->nullable(),
                    ]),
            ]);
    }
}

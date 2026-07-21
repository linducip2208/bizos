<?php

namespace App\Filament\Resources\Interviewers\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class InterviewerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Pewawancara')
                    ->columns(2)
                    ->schema([
                        Select::make('interview_id')
                            ->label('Interview')
                            ->relationship('interview', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->first_name . ' ' . $record->last_name))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('role')
                            ->label('Peran')
                            ->options([
                                'lead' => 'Pewawancara Utama',
                                'technical' => 'Teknis',
                                'hr' => 'HR',
                                'observer' => 'Observer',
                            ])
                            ->required(),
                    ]),
            ]);
    }
}
<?php

namespace App\Filament\Resources\BudgetVersions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BudgetVersionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Versi Anggaran')
                    ->columns(2)
                    ->schema([
                        Select::make('budget_id')
                            ->label('Anggaran')
                            ->relationship('budget', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('version_number')
                            ->label('Nomor Versi')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        TextInput::make('name')
                            ->label('Nama Versi')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'approved' => 'Disetujui',
                                'active' => 'Aktif',
                                'closed' => 'Ditutup',
                            ])
                            ->required()
                            ->default('draft'),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->rows(3),
                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approvedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DatePicker::make('approved_at')
                            ->label('Tanggal Disetujui')
                            ->nullable(),
                        Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('createdBy', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
            ]);
    }
}

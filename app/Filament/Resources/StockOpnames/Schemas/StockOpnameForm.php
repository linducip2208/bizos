<?php

namespace App\Filament\Resources\StockOpnames\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockOpnameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Opname')
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Gudang')
                            ->relationship('warehouse', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        DatePicker::make('opname_date')
                            ->label('Tanggal Opname')
                            ->required()
                            ->default(now()),
                        Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('creator', 'full_name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'in_progress' => 'Dalam Proses',
                                'completed' => 'Selesai',
                                'adjusted' => 'Disesuaikan',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('draft')
                            ->required(),
                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approver', 'full_name')
                            ->preload()
                            ->searchable()
                            ->nullable(),
                    ])->columns(2),
            ]);
    }
}

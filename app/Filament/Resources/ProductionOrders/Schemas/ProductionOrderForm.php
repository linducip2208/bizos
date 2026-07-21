<?php

namespace App\Filament\Resources\ProductionOrders\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductionOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Production Order')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('bom_id')
                            ->label('Bill of Material')
                            ->relationship('bom', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('work_center_id')
                            ->label('Work Center')
                            ->relationship('workCenter', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('planned_quantity')
                            ->label('Kuantitas Direncanakan')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'planned' => 'Planned',
                                'in_progress' => 'In Progress',
                                'completed' => 'Selesai',
                                'cancelled' => 'Batal',
                            ])
                            ->default('draft')
                            ->required(),
                        DateTimePicker::make('planned_start')
                            ->label('Mulai Direncanakan')
                            ->native(false),
                        DateTimePicker::make('planned_end')
                            ->label('Selesai Direncanakan')
                            ->native(false),
                        TextInput::make('produced_quantity')
                            ->label('Qty Diproduksi')
                            ->numeric()
                            ->inputMode('decimal')
                            ->disabled(),
                        TextInput::make('rejected_quantity')
                            ->label('Qty Reject')
                            ->numeric()
                            ->inputMode('decimal')
                            ->disabled(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
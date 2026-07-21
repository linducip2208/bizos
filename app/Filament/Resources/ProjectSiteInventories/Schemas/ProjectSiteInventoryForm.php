<?php

namespace App\Filament\Resources\ProjectSiteInventories\Schemas;

use App\Models\Product;
use App\Models\Project;
use App\Models\Warehouse;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectSiteInventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        $companyId = auth()->user()->company_id;

        return $schema
            ->components([
                Section::make('Referensi')
                    ->schema([
                        Select::make('project_id')
                            ->label('Proyek')
                            ->options(Project::where('company_id', $companyId)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('product_id')
                            ->label('Produk/Material')
                            ->options(Product::where('company_id', $companyId)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('warehouse_id')
                            ->label('Gudang Asal')
                            ->options(Warehouse::where('company_id', $companyId)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ])->columns(3),

                Section::make('Kuantitas')
                    ->schema([
                        TextInput::make('quantity_on_site')
                            ->label('Stok di Site')
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('quantity_used')
                            ->label('Terpakai')
                            ->required()
                            ->numeric()
                            ->default(0),
                        DatePicker::make('last_delivery_date')
                            ->label('Pengiriman Terakhir'),
                    ])->columns(3),

                Section::make('Catatan')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

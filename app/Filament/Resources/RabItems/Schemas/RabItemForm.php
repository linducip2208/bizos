<?php

namespace App\Filament\Resources\RabItems\Schemas;

use App\Models\Project;
use App\Models\RabItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RabItemForm
{
    public static function configure(Schema $schema): Schema
    {
        $companyId = auth()->user()->company_id;

        return $schema
            ->components([
                Section::make('Informasi Item RAB')
                    ->schema([
                        Select::make('project_id')
                            ->label('Proyek')
                            ->options(Project::where('company_id', $companyId)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('parent_id')
                            ->label('Induk')
                            ->options(RabItem::where('company_id', $companyId)->pluck('description', 'id'))
                            ->searchable()
                            ->nullable(),
                        TextInput::make('item_code')
                            ->label('Kode Item')
                            ->required()
                            ->maxLength(50),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->maxLength(500),
                    ])->columns(2),

                Section::make('Kuantitas & Harga')
                    ->schema([
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->required()
                            ->maxLength(30)
                            ->placeholder('m2, m3, kg, ls, unit'),
                        TextInput::make('quantity')
                            ->label('Volume/Kuantitas')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('unit_price')
                            ->label('Harga Satuan (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('total_amount')
                            ->label('Jumlah Total (Rp)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(true)
                            ->prefix('Rp'),
                    ])->columns(2),

                Section::make('Klasifikasi')
                    ->schema([
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'material' => 'Material',
                                'labor' => 'Tenaga Kerja',
                                'equipment' => 'Alat Berat',
                                'subcontract' => 'Subkontraktor',
                                'overhead' => 'Overhead',
                            ])
                            ->default('material')
                            ->required(),
                        TextInput::make('weight_percent')
                            ->label('Bobot (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->placeholder('Persentase terhadap total RAB'),
                        TextInput::make('sort_order')
                            ->label('Urut')
                            ->integer()
                            ->default(0),
                    ])->columns(3),
            ]);
    }
}
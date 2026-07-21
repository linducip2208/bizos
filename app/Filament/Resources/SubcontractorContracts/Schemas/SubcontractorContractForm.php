<?php

namespace App\Filament\Resources\SubcontractorContracts\Schemas;

use App\Models\Project;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubcontractorContractForm
{
    public static function configure(Schema $schema): Schema
    {
        $companyId = auth()->user()->company_id;

        return $schema
            ->components([
                Section::make('Informasi Kontrak')
                    ->schema([
                        Select::make('project_id')
                            ->label('Proyek')
                            ->options(Project::where('company_id', $companyId)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('supplier_id')
                            ->label('Subkontraktor')
                            ->options(Supplier::where('company_id', $companyId)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('contract_number')
                            ->label('Nomor Kontrak')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Aktif',
                                'completed' => 'Selesai',
                                'terminated' => 'Dihentikan',
                            ])
                            ->default('draft')
                            ->required(),
                    ])->columns(2),

                Section::make('Nilai & Periode')
                    ->schema([
                        TextInput::make('contract_amount')
                            ->label('Nilai Kontrak (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('retention_percent')
                            ->label('Retensi (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(5)
                            ->suffix('%'),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->afterOrEqual('start_date'),
                    ])->columns(2),

                Section::make('Lingkup Pekerjaan')
                    ->schema([
                        Textarea::make('scope_of_work')
                            ->label('Lingkup Pekerjaan')
                            ->required()
                            ->maxLength(3000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
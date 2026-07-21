<?php

namespace App\Filament\Resources\MaintenanceRequests\Schemas;

use App\Models\Employee;
use App\Models\PropertyUnit;
use App\Models\TenancyContract;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaintenanceRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        $companyId = auth()->user()->company_id;

        return $schema
            ->components([
                Section::make('Informasi Permintaan')
                    ->schema([
                        Select::make('property_unit_id')
                            ->label('Unit Properti')
                            ->options(PropertyUnit::where('company_id', $companyId)->pluck('unit_number', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('tenancy_contract_id')
                            ->label('Kontrak Sewa')
                            ->options(TenancyContract::where('company_id', $companyId)->pluck('contract_number', 'id'))
                            ->searchable()
                            ->nullable(),
                        TextInput::make('requested_by')
                            ->label('Diminta Oleh')
                            ->required()
                            ->maxLength(200),
                    ])->columns(3),

                Section::make('Detail Perbaikan')
                    ->schema([
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'plumbing' => 'Pipa/Air',
                                'electrical' => 'Listrik',
                                'ac' => 'AC',
                                'structural' => 'Struktural',
                                'pest' => 'Hama',
                                'other' => 'Lainnya',
                            ])
                            ->default('other')
                            ->required(),
                        Select::make('priority')
                            ->label('Prioritas')
                            ->options([
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                                'emergency' => 'Darurat',
                            ])
                            ->default('medium')
                            ->required(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Penugasan & Status')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'reported' => 'Dilaporkan',
                                'assigned' => 'Ditugaskan',
                                'in_progress' => 'Dalam Pengerjaan',
                                'completed' => 'Selesai',
                                'verified' => 'Terverifikasi',
                            ])
                            ->default('reported')
                            ->required(),
                        Select::make('assigned_to')
                            ->label('Ditugaskan Ke')
                            ->options(Employee::where('company_id', $companyId)->get()->mapWithKeys(fn ($e) => [$e->id => "{$e->first_name} {$e->last_name}"]))
                            ->searchable()
                            ->nullable(),
                        TextInput::make('cost')
                            ->label('Biaya (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}

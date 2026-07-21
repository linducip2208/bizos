<?php

namespace App\Filament\Resources\ProgressBillings\Schemas;

use App\Models\Project;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgressBillingForm
{
    public static function configure(Schema $schema): Schema
    {
        $companyId = auth()->user()->company_id;

        return $schema
            ->components([
                Section::make('Informasi Tagihan')
                    ->schema([
                        Select::make('project_id')
                            ->label('Proyek')
                            ->options(Project::where('company_id', $companyId)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('billing_number')
                            ->label('Nomor Tagihan')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        DatePicker::make('billing_period_start')
                            ->label('Periode Mulai')
                            ->required(),
                        DatePicker::make('billing_period_end')
                            ->label('Periode Selesai')
                            ->required()
                            ->afterOrEqual('billing_period_start'),
                    ])->columns(2),

                Section::make('Progres Fisik')
                    ->schema([
                        TextInput::make('physical_progress_percent')
                            ->label('Progres Fisik (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        TextInput::make('previous_claimed_percent')
                            ->label('Klaim Sebelumnya (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        TextInput::make('current_claimed_percent')
                            ->label('Klaim Saat Ini (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                    ])->columns(3),

                Section::make('Nilai Tagihan')
                    ->schema([
                        TextInput::make('gross_amount')
                            ->label('Nilai Kotor (Rp)')
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
                        TextInput::make('retention_amount')
                            ->label('Nilai Retensi (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('net_amount')
                            ->label('Nilai Bersih (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                    ])->columns(2),

                Section::make('Status & Approval')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Diajukan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'paid' => 'Dibayar',
                            ])
                            ->default('draft')
                            ->required(),
                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->options(User::where('company_id', $companyId)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ])->columns(2),
            ]);
    }
}
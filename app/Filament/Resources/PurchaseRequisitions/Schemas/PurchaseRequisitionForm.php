<?php

namespace App\Filament\Resources\PurchaseRequisitions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseRequisitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Permintaan')
                    ->schema([
                        Select::make('department_id')
                            ->label('Departemen')
                            ->relationship('department', 'name')
                            ->preload()
                            ->searchable()
                            ->nullable(),
                        Select::make('requested_by')
                            ->label('Diminta Oleh')
                            ->relationship('requester', 'full_name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        DatePicker::make('date_required')
                            ->label('Tanggal Dibutuhkan')
                            ->nullable(),
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
                                'submitted' => 'Diajukan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'converted' => 'Dikonversi',
                            ])
                            ->default('draft')
                            ->required(),
                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approver', 'full_name')
                            ->preload()
                            ->searchable()
                            ->nullable()
                            ->visible(fn ($get) => in_array($get('status'), ['approved', 'rejected'])),
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('status') === 'rejected'),
                    ])->columns(2),
            ]);
    }
}

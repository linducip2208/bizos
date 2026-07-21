<?php

namespace App\Filament\Resources\Milestones\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MilestoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Milestone')
                    ->columns(3)
                    ->schema([
                        Select::make('project_id')
                            ->label('Proyek')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Milestone')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('target_date')
                            ->label('Tanggal Target')
                            ->required(),
                        DatePicker::make('completed_date')
                            ->label('Tanggal Selesai'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Tertunda',
                                'in_progress' => 'Sedang Berjalan',
                                'completed' => 'Selesai',
                                'overdue' => 'Terlambat',
                            ])
                            ->default('pending')
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

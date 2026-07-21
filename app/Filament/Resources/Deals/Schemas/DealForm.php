<?php

namespace App\Filament\Resources\Deals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DealForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Deal')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Select::make('lead_id')
                            ->label('Lead')
                            ->relationship('lead', 'email')
                            ->searchable()
                            ->preload(),
                        Select::make('client_id')
                            ->label('Klien')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('stage_id')
                            ->label('Tahap')
                            ->relationship('stage', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('assigned_to')
                            ->label('Ditugaskan Kepada')
                            ->relationship('assignedTo', 'first_name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('expected_value')
                            ->label('Nilai Estimasi')
                            ->numeric()
                            ->prefix('Rp'),
                        DatePicker::make('expected_close_date')
                            ->label('Target Tutup'),
                        DatePicker::make('actual_close_date')
                            ->label('Tanggal Tutup Aktual'),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('terbuka')
                            ->options([
                                'terbuka' => 'Terbuka',
                                'menang' => 'Menang',
                                'kalah' => 'Kalah',
                                'tertunda' => 'Tertunda',
                            ]),
                        TextInput::make('lost_reason')
                            ->label('Alasan Kalah')
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('status') === 'kalah'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

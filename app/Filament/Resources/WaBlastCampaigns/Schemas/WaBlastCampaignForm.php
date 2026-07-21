<?php

namespace App\Filament\Resources\WaBlastCampaigns\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WaBlastCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kampanye')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Kampanye')
                            ->required()
                            ->maxLength(255),
                        Select::make('template_id')
                            ->label('Template')
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('target_type')
                            ->label('Tipe Target')
                            ->required()
                            ->default('segment')
                            ->options([
                                'segment' => 'Segment',
                                'manual' => 'Manual',
                            ]),
                        Select::make('target_segment_id')
                            ->label('Segment Target')
                            ->relationship('targetSegment', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('target_type') === 'segment'),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('draft')
                            ->options([
                                'draft' => 'Draft',
                                'terjadwal' => 'Terjadwal',
                                'dikirim' => 'Dikirim',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                            ]),
                        DateTimePicker::make('scheduled_at')
                            ->label('Jadwal Kirim'),
                    ]),
            ]);
    }
}
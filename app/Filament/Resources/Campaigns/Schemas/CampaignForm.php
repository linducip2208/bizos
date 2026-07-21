<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CampaignForm
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
                        Select::make('channel')
                            ->label('Kanal')
                            ->required()
                            ->options([
                                'email' => 'Email',
                                'whatsapp' => 'WhatsApp',
                                'sms' => 'SMS',
                                'multi' => 'Multi Channel',
                            ]),
                        Select::make('email_campaign_id')
                            ->label('Email Campaign')
                            ->relationship('emailCampaign', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('wa_blast_campaign_id')
                            ->label('WA Blast Campaign')
                            ->relationship('waBlastCampaign', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('draft')
                            ->options([
                                'draft' => 'Draft',
                                'scheduled' => 'Dijadwalkan',
                                'running' => 'Berjalan',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ]),
                        Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('createdBy', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Textarea::make('target_audience')
                            ->label('Target Audiens (JSON)')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

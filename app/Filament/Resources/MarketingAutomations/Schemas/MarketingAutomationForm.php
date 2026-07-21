<?php

namespace App\Filament\Resources\MarketingAutomations\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MarketingAutomationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konfigurasi Automation')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Select::make('trigger_type')
                            ->label('Tipe Trigger')
                            ->required()
                            ->options([
                                'lead_created' => 'Lead Baru',
                                'deal_stage_changed' => 'Deal Stage Berubah',
                                'form_submitted' => 'Form Disubmit',
                                'email_opened' => 'Email Dibuka',
                                'link_clicked' => 'Link Diklik',
                                'schedule' => 'Jadwal',
                                'webhook' => 'Webhook',
                            ]),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('draft')
                            ->options([
                                'active' => 'Aktif',
                                'paused' => 'Ditunda',
                                'draft' => 'Draft',
                            ]),
                        Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('createdBy', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Textarea::make('trigger_config')
                            ->label('Trigger Config (JSON)')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('actions')
                            ->label('Actions (JSON)')
                            ->rows(4)
                            ->hint('Contoh: [{"type":"send_email","template":"welcome"},{"type":"add_tag","tag":"hot"}]')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

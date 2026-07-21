<?php

namespace App\Filament\Resources\Ticket\Schemas;

use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Employee;
use App\Models\TicketCategory;
use App\Models\TicketTag;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tiket')
                    ->columns(2)
                    ->schema([
                        TextInput::make('subject')
                            ->label('Subjek')
                            ->required()
                            ->maxLength(500),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('priority')
                            ->label('Prioritas')
                            ->required()
                            ->options([
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                                'urgent' => 'Urgent',
                            ])
                            ->default('medium'),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'open' => 'Terbuka',
                                'in_progress' => 'Dalam Proses',
                                'waiting_on_customer' => 'Menunggu Pelanggan',
                                'resolved' => 'Terselesaikan',
                                'closed' => 'Tertutup',
                            ])
                            ->default('open'),
                        Select::make('source')
                            ->label('Sumber')
                            ->required()
                            ->options([
                                'portal' => 'Portal',
                                'email' => 'Email',
                                'phone' => 'Telepon',
                                'chat' => 'Chat',
                                'internal' => 'Internal',
                            ])
                            ->default('portal'),
                        Select::make('client_id')
                            ->label('Klien')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->reactive(),
                        Select::make('contact_id')
                            ->label('Kontak')
                            ->options(function (callable $get) {
                                $clientId = $get('client_id');
                                if (!$clientId) return [];
                                return ClientContact::where('client_id', $clientId)
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [$c->id => $c->first_name . ' ' . $c->last_name]);
                            })
                            ->searchable()
                            ->nullable(),
                        Select::make('assigned_to')
                            ->label('Ditugaskan Kepada')
                            ->relationship('assignedTo', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('tags')
                            ->label('Label')
                            ->multiple()
                            ->relationship('tags', 'name')
                            ->preload()
                            ->nullable()
                            ->columnSpanFull(),
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
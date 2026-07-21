<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengumuman')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(500),
                        Select::make('priority')
                            ->label('Prioritas')
                            ->options([
                                'low' => 'Rendah',
                                'normal' => 'Normal',
                                'high' => 'Tinggi',
                                'urgent' => 'Penting',
                            ])
                            ->default('normal')
                            ->required(),
                        Select::make('target_type')
                            ->label('Target')
                            ->options([
                                'all' => 'Semua',
                                'department' => 'Per Departemen',
                                'position' => 'Per Posisi',
                                'designation' => 'Per Jabatan',
                                'specific' => 'Spesifik',
                            ])
                            ->default('all')
                            ->required(),
                        Textarea::make('content')
                            ->label('Konten')
                            ->rows(8)
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make('Publikasi')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->nullable(),
                        DateTimePicker::make('expires_at')
                            ->label('Tanggal Kadaluarsa')
                            ->nullable(),
                        Select::make('published_by')
                            ->label('Dipublikasi Oleh')
                            ->relationship('publishedBy', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\IsoPolicies\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IsoPolicyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kebijakan')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(1),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('policy_number')
                            ->label('Nomor Kebijakan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'access_control' => 'Kontrol Akses',
                                'data_classification' => 'Klasifikasi Data',
                                'incident_response' => 'Respons Insiden',
                                'acceptable_use' => 'Penggunaan yang Dapat Diterima',
                                'password' => 'Kata Sandi',
                                'remote_work' => 'Kerja Jarak Jauh',
                                'backup' => 'Pencadangan',
                                'vendor_management' => 'Manajemen Pemasok',
                                'data_protection' => 'Perlindungan Data',
                                'business_continuity' => 'Kelangsungan Bisnis',
                            ])
                            ->required(),
                        TextInput::make('version')
                            ->label('Versi')
                            ->default('1.0')
                            ->maxLength(20),
                        DatePicker::make('effective_date')
                            ->label('Tanggal Efektif')
                            ->required()
                            ->default(now()),
                        DatePicker::make('review_due')
                            ->label('Review Berikutnya'),
                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Aktif',
                                'under_review' => 'Dalam Review',
                                'archived' => 'Arsip',
                            ])
                            ->default('draft'),
                    ]),
                Section::make('Isi Kebijakan')
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(2),
                        RichEditor::make('content')
                            ->label('Isi Kebijakan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

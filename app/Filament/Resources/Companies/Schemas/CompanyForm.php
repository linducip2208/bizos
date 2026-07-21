<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Perusahaan')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama Perusahaan')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn ($record) => $record !== null)
                            ->helperText('Slug otomatis dari nama. Tidak dapat diubah setelah dibuat.'),
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('companies/logos')
                            ->imagePreviewHeight('100')
                            ->maxSize(2048),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('tax_id')
                            ->label('NPWP')
                            ->maxLength(50),
                    ]),
                Section::make('Status & Langganan')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Toggle::make('is_suspended')
                            ->label('Suspend')
                            ->default(false)
                            ->visible(fn ($record) => $record !== null)
                            ->helperText('Suspend akan menonaktifkan semua akses pengguna'),
                        DatePicker::make('subscription_start')
                            ->label('Tanggal Mulai Langganan'),
                        DatePicker::make('subscription_end')
                            ->label('Tanggal Akhir Langganan'),
                    ]),
                Section::make('Pengaturan Suspensi')
                    ->columns(1)
                    ->visible(fn ($record) => $record && $record->is_suspended)
                    ->schema([
                        Textarea::make('suspended_reason')
                            ->label('Alasan Suspend')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('suspended_at')
                            ->label('Tanggal Suspend')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d M Y H:i') : '-'),
                        TextInput::make('data_retention_days')
                            ->label('Retensi Data (hari)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(365)
                            ->helperText('Data akan dihapus setelah masa retensi berakhir sejak tanggal suspend'),
                    ]),
            ]);
    }
}

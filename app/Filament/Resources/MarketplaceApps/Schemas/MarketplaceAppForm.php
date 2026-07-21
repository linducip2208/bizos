<?php

namespace App\Filament\Resources\MarketplaceApps\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MarketplaceAppForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Aplikasi')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Aplikasi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->maxLength(5000),
                        TextInput::make('developer')
                            ->label('Developer')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('version')
                            ->label('Versi')
                            ->required()
                            ->default('1.0.0')
                            ->maxLength(20),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'crm' => 'CRM',
                                'finance' => 'Keuangan',
                                'hr' => 'SDM',
                                'inventory' => 'Inventaris',
                                'marketing' => 'Marketing',
                                'project' => 'Proyek',
                                'communication' => 'Komunikasi',
                                'reporting' => 'Laporan',
                                'integration' => 'Integrasi',
                                'security' => 'Keamanan',
                                'utility' => 'Utilitas',
                                'other' => 'Lainnya',
                            ])
                            ->required(),
                    ]),
                Section::make('Harga & Fitur')
                    ->columns(2)
                    ->schema([
                        Select::make('price_type')
                            ->label('Tipe Harga')
                            ->options([
                                'free' => 'Gratis',
                                'paid' => 'Sekali Bayar',
                                'monthly' => 'Berlangganan Bulanan',
                            ])
                            ->required()
                            ->default('free'),
                        TextInput::make('price')
                            ->label('Harga (Rp)')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp'),
                        KeyValue::make('features')
                            ->label('Fitur')
                            ->keyLabel('Nama Fitur')
                            ->valueLabel('Deskripsi'),
                        KeyValue::make('requirements')
                            ->label('Persyaratan')
                            ->keyLabel('Item')
                            ->valueLabel('Keterangan'),
                    ]),
                Section::make('Media & Package')
                    ->columns(3)
                    ->schema([
                        TextInput::make('icon')
                            ->label('URL Icon')
                            ->url()
                            ->maxLength(255),
                        TextArea::make('screenshots')
                            ->label('URL Screenshots (1 per baris)')
                            ->rows(4)
                            ->helperText('Satu URL per baris'),
                        TextInput::make('package_path')
                            ->label('Package Path')
                            ->maxLength(255),
                        TextInput::make('migration_class')
                            ->label('Migration Class')
                            ->maxLength(255),
                        TextInput::make('seeder_class')
                            ->label('Seeder Class')
                            ->maxLength(255),
                    ]),
                Section::make('Publikasi')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Dipublikasikan')
                            ->default(false),
                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),
                        KeyValue::make('permissions_required')
                            ->label('Permissions')
                            ->keyLabel('Permission Slug')
                            ->valueLabel('Deskripsi'),
                    ]),
            ]);
    }
}

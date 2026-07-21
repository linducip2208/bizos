<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Paket')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Paket')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn ($record) => $record !== null)
                            ->helperText('Slug otomatis. Tidak dapat diubah setelah dibuat.'),
                        Select::make('tier')
                            ->label('Tier')
                            ->options([
                                'standard' => 'Standard',
                                'gold' => 'Gold',
                                'platinum' => 'Platinum',
                            ])
                            ->required()
                            ->default('standard'),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
                Section::make('Harga')
                    ->columns(2)
                    ->schema([
                        TextInput::make('monthly_price')
                            ->label('Harga Bulanan (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('yearly_price')
                            ->label('Harga Tahunan (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                    ]),
                Section::make('Batasan')
                    ->columns(3)
                    ->schema([
                        TextInput::make('max_users')
                            ->label('Maksimal Pengguna')
                            ->required()
                            ->numeric()
                            ->default(5),
                        TextInput::make('max_companies')
                            ->label('Maksimal Perusahaan')
                            ->required()
                            ->numeric()
                            ->default(1),
                        TextInput::make('max_branches')
                            ->label('Maksimal Cabang')
                            ->required()
                            ->numeric()
                            ->default(1),
                    ]),
                Section::make('Fitur')
                    ->schema([
                        TagsInput::make('features')
                            ->label('Daftar Fitur')
                            ->placeholder('Tambah fitur...')
                            ->helperText('Tekan Enter untuk menambahkan fitur.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

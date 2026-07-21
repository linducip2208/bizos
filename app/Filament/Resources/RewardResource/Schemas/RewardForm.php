<?php

namespace App\Filament\Resources\RewardResource\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Reward')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Reward')
                            ->required()
                            ->maxLength(255),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'merchandise' => 'Merchandise',
                                'voucher' => 'Voucher',
                                'makanan' => 'Makanan & Minuman',
                                'pengalaman' => 'Pengalaman',
                                'digital' => 'Digital',
                                'lainnya' => 'Lainnya',
                            ])
                            ->nullable(),
                        TextInput::make('points_cost')
                            ->label('Biaya Poin')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->minValue(1)
                            ->helperText('Jumlah poin yang diperlukan untuk menukar reward ini.'),
                        TextInput::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->helperText('0 = tidak terbatas, -1 = unlimited.'),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->rows(3)
                            ->maxLength(1000),
                        FileUpload::make('image')
                            ->label('Gambar')
                            ->image()
                            ->directory('rewards')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}

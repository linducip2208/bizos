<?php

namespace App\Filament\Resources\WaAutoReplies\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WaAutoReplyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Auto Reply')
                    ->columns(2)
                    ->schema([
                        TextInput::make('keyword')
                            ->label('Kata Kunci')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Kata yang akan mentrigger auto reply'),
                        Select::make('match_type')
                            ->label('Tipe Pencocokan')
                            ->required()
                            ->default('exact')
                            ->options([
                                'exact' => 'Persis',
                                'contains' => 'Mengandung',
                                'starts_with' => 'Dimulai Dengan',
                            ]),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Textarea::make('reply_text')
                            ->label('Teks Balasan')
                            ->rows(5)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

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
                        Toggle::make('is_ai_powered')
                            ->label('Ditenagai AI')
                            ->default(false)
                            ->helperText('Balasan dibuat oleh AI (bukan template statis)')
                            ->live(),
                    ]),

                Section::make('Konfigurasi AI')
                    ->description('Hanya tampil saat mode AI diaktifkan.')
                    ->columns(2)
                    ->visible(fn ($get) => (bool) $get('is_ai_powered'))
                    ->schema([
                        Select::make('ai_provider_id')
                            ->label('AI Provider')
                            ->relationship('aiProvider', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Provider AI yang dipakai untuk generate balasan'),
                        Textarea::make('ai_prompt_template')
                            ->label('Template Prompt AI')
                            ->rows(5)
                            ->columnSpanFull()
                            ->helperText('Gunakan variabel: {name}, {company}, {order_status}, {order_number}, {invoice_status}, {invoice_number}, {invoice_total}, {invoice_remaining}, {now}'),
                        Textarea::make('fallback_message')
                            ->label('Pesan Fallback')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Dikirim saat AI gagal merespon atau percakapan di-eskalasi ke manusia'),
                    ]),

                Section::make('Teks Balasan Template')
                    ->description('Hanya dipakai saat mode template (non-AI).')
                    ->columns(1)
                    ->visible(fn ($get) => ! (bool) $get('is_ai_powered'))
                    ->schema([
                        Textarea::make('reply_text')
                            ->label('Teks Balasan')
                            ->rows(5)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

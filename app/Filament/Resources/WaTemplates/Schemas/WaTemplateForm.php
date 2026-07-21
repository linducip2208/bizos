<?php

namespace App\Filament\Resources\WaTemplates\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WaTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Template')
                    ->columns(1)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Template')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nama unik template WA (gunakan underscore, huruf kecil)'),
                        Select::make('category')
                            ->label('Kategori')
                            ->required()
                            ->default('marketing')
                            ->options([
                                'marketing' => 'Marketing',
                                'transaksional' => 'Transaksional',
                                'layanan' => 'Layanan',
                                'pengingat' => 'Pengingat',
                            ]),
                        Select::make('language')
                            ->label('Bahasa')
                            ->required()
                            ->default('id')
                            ->options([
                                'id' => 'Indonesia',
                                'en' => 'Inggris',
                            ]),
                        Textarea::make('content')
                            ->label('Isi Pesan')
                            ->rows(6)
                            ->required()
                            ->helperText('Gunakan {{1}} untuk parameter. Contoh: Halo {{1}}, pesanan Anda #{{2}} sedang diproses.'),
                    ]),
                Section::make('Status Meta')
                    ->columns(1)
                    ->visible(fn ($record) => $record && !empty($record->meta_template_id))
                    ->schema([
                        TextInput::make('meta_template_id')
                            ->label('Meta Template ID')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('meta_template_status')
                            ->label('Status di Meta')
                            ->options([
                                'draft' => 'Draft',
                                'pending_approval' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'paused' => 'Dinonaktifkan',
                            ])
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('meta_rejection_reason')
                            ->label('Alasan Penolakan Meta')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record && $record->meta_template_status === 'rejected'),
                        TextInput::make('quality_score')
                            ->label('Skor Kualitas')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}

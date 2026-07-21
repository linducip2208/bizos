<?php

namespace App\Filament\Resources\NoticeBoardResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NoticeBoardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pengumuman')->columns(2)->schema([
                Select::make('company_id')->label('Perusahaan')->relationship('company','name')->searchable()->preload()->required(),
                TextInput::make('title')->label('Judul')->required()->maxLength(255)->columnSpanFull(),
                Select::make('category')->label('Kategori')->options(['general'=>'Umum','hr'=>'HR','it'=>'IT','urgent'=>'Darurat','event'=>'Acara'])->default('general')->required(),
                Select::make('priority')->label('Prioritas')->options(['normal'=>'Normal','important'=>'Penting','urgent'=>'Darurat'])->default('normal')->required(),
                Select::make('posted_by')->label('Diposting Oleh')->relationship('postedBy','name')->searchable()->preload(),
                DateTimePicker::make('expires_at')->label('Kedaluwarsa'),
                Toggle::make('is_pinned')->label('Sematkan (Pin)')->default(false),
                Textarea::make('content')->label('Konten')->required()->columnSpanFull()->rows(5),
            ]),
        ]);
    }
}

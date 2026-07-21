<?php

namespace App\Filament\Resources\DocumentGenerations;

use App\Filament\Resources\DocumentGenerations\Pages\ListDocumentGenerations;
use App\Filament\Resources\DocumentGenerations\Pages\ViewDocumentGeneration;
use App\Models\DocumentGeneration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DocumentGenerationResource extends Resource
{
    protected static ?string $model = DocumentGeneration::class;

    protected static ?string $label = 'Dokumen Terbit';

    protected static ?string $pluralLabel = 'Dokumen Terbit';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 1053;

    public static function getNavigationGroup(): string|null
    {
        return '⚙️ Sistem';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentGenerations::route('/'),
            'view' => ViewDocumentGeneration::route('/{record}'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return DocumentGenerationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentGenerationTable::configure($table);
    }
}
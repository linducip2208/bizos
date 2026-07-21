<?php

namespace App\Filament\Resources\TranslationResource;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\TranslationResource\Pages\ListTranslations;
use App\Filament\Resources\TranslationResource\Pages\CreateTranslation;
use App\Filament\Resources\TranslationResource\Pages\EditTranslation;
use App\Filament\Resources\TranslationResource\Schemas\TranslationForm;
use App\Filament\Resources\TranslationResource\Tables\TranslationTable;
use App\Models\Translation;

class TranslationResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = Translation::class;
    public static function getNavigationGroup(): string|null { return 'Sistem'; }
    protected static ?string $label = 'Terjemahan';
    protected static ?string $pluralLabel = 'Terjemahan';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;
    protected static ?int $navigationSort = 1054;
    protected static ?string $recordTitleAttribute = 'key';
    public static function form(Schema $schema): Schema { return TranslationForm::configure($schema); }
    public static function table(Table $table): Table { return TranslationTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return [
        'index' => ListTranslations::route('/'),
        'create' => CreateTranslation::route('/create'),
        'edit' => EditTranslation::route('/{record}/edit'),
    ];}
}

<?php

namespace App\Filament\Resources\AiProviders;

use App\Filament\Resources\AiProviders\Pages\CreateAiProvider;
use App\Filament\Resources\AiProviders\Pages\EditAiProvider;
use App\Filament\Resources\AiProviders\Pages\ListAiProviders;
use App\Filament\Resources\AiProviders\Schemas\AiProviderForm;
use App\Filament\Resources\AiProviders\Tables\AiProvidersTable;
use App\Models\AiProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class AiProviderResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = AiProvider::class;

    protected static ?string $label = 'AI Providers';

    protected static ?string $pluralLabel = 'AI Providers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 901;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::AUTOMATION->value;
    }

    public static function form(Schema $schema): Schema
    {
        return AiProviderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AiProvidersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiProviders::route('/'),
            'create' => CreateAiProvider::route('/create'),
            'edit' => EditAiProvider::route('/{record}/edit'),
        ];
    }
}
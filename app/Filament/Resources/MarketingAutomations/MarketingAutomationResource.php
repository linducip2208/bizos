<?php

namespace App\Filament\Resources\MarketingAutomations;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\MarketingAutomations\Pages\CreateMarketingAutomation;
use App\Filament\Resources\MarketingAutomations\Pages\EditMarketingAutomation;
use App\Filament\Resources\MarketingAutomations\Pages\ListMarketingAutomations;
use App\Filament\Resources\MarketingAutomations\Schemas\MarketingAutomationForm;
use App\Filament\Resources\MarketingAutomations\Tables\MarketingAutomationsTable;
use App\Models\MarketingAutomation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MarketingAutomationResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = MarketingAutomation::class;

    public static function getNavigationGroup(): string|null
    {
        return '📈 Sales & CRM';
    }

    protected static ?string $label = 'Automation';

    protected static ?string $pluralLabel = 'Automation';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog;

    protected static ?int $navigationSort = 422;

    public static function form(Schema $schema): Schema
    {
        return MarketingAutomationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketingAutomationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketingAutomations::route('/'),
            'create' => CreateMarketingAutomation::route('/create'),
            'edit' => EditMarketingAutomation::route('/{record}/edit'),
        ];
    }
}

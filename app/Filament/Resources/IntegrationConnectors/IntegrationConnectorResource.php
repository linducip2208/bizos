<?php

namespace App\Filament\Resources\IntegrationConnectors;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\IntegrationConnectors\Pages\CreateIntegrationConnector;
use App\Filament\Resources\IntegrationConnectors\Pages\EditIntegrationConnector;
use App\Filament\Resources\IntegrationConnectors\Pages\ListIntegrationConnectors;
use App\Filament\Resources\IntegrationConnectors\Schemas\IntegrationConnectorForm;
use App\Filament\Resources\IntegrationConnectors\Tables\IntegrationConnectorsTable;
use App\Models\IntegrationConnector;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IntegrationConnectorResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = IntegrationConnector::class;

    public static function getNavigationGroup(): string|null
    {
        return '🔗 Integrations';
    }

    protected static ?string $label = 'Konektor';

    protected static ?string $pluralLabel = 'Konektor Integrasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return IntegrationConnectorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IntegrationConnectorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIntegrationConnectors::route('/'),
            'create' => CreateIntegrationConnector::route('/create'),
            'edit' => EditIntegrationConnector::route('/{record}/edit'),
        ];
    }
}
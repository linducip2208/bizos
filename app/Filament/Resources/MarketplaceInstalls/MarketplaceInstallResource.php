<?php

namespace App\Filament\Resources\MarketplaceInstalls;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\MarketplaceInstalls\Pages\ListMarketplaceInstalls;
use App\Filament\Resources\MarketplaceInstalls\Pages\ViewMarketplaceInstall;
use App\Filament\Resources\MarketplaceInstalls\Tables\MarketplaceInstallsTable;
use App\Models\MarketplaceInstall;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MarketplaceInstallResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = MarketplaceInstall::class;

    protected static ?string $label = 'Install Aplikasi';

    protected static ?string $pluralLabel = 'Install Aplikasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownCircle;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string|null
    {
        return 'Platform';
    }

    public static function table(Table $table): Table
    {
        return MarketplaceInstallsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketplaceInstalls::route('/'),
            'view' => ViewMarketplaceInstall::route('/{record}'),
        ];
    }
}

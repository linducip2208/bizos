<?php

namespace App\Filament\Resources\SalesTargets;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\SalesTargets\Pages\CreateSalesTarget;
use App\Filament\Resources\SalesTargets\Pages\EditSalesTarget;
use App\Filament\Resources\SalesTargets\Pages\ListSalesTargets;
use App\Filament\Resources\SalesTargets\Schemas\SalesTargetForm;
use App\Filament\Resources\SalesTargets\Tables\SalesTargetsTable;
use App\Models\SalesTarget;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalesTargetResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = SalesTarget::class;

    public static function getNavigationGroup(): string|null
    {
        return '📈 Sales & CRM';
    }

    protected static ?string $label = 'Target Penjualan';

    protected static ?string $pluralLabel = 'Target Penjualan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 415;

    public static function form(Schema $schema): Schema
    {
        return SalesTargetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesTargetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesTargets::route('/'),
            'create' => CreateSalesTarget::route('/create'),
            'edit' => EditSalesTarget::route('/{record}/edit'),
        ];
    }
}

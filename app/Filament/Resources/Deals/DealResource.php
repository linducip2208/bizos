<?php

namespace App\Filament\Resources\Deals;

use App\Filament\Resources\Deals\Pages\CreateDeal;
use App\Filament\Resources\Deals\Pages\EditDeal;
use App\Filament\Resources\Deals\Pages\ListDeals;
use App\Filament\Resources\Deals\Pages\ViewDealKanban;
use App\Filament\Resources\Deals\Schemas\DealForm;
use App\Filament\Resources\Deals\Tables\DealsTable;
use App\Models\Deal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class DealResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = Deal::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? Sales & CRM';
    }

    protected static ?string $label = 'Deal';

    protected static ?string $pluralLabel = 'Deal';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static ?int $navigationSort = 405;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return DealForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DealsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeals::route('/'),
            'create' => CreateDeal::route('/create'),
            'edit' => EditDeal::route('/{record}/edit'),
            'kanban' => ViewDealKanban::route('/kanban'),
        ];
    }
}
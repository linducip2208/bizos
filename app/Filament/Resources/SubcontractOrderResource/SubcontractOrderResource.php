<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubcontractOrderResource\Pages;
use App\Models\SubcontractOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class SubcontractOrderResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = SubcontractOrder::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Manufaktur';
    }

    protected static ?string $label = 'Subkontrak';

    protected static ?string $pluralLabel = 'Subkontrak';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\SubcontractOrderResource\Schemas\SubcontractOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\SubcontractOrderResource\Tables\SubcontractOrderTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubcontractOrders::route('/'),
            'create' => Pages\CreateSubcontractOrder::route('/create'),
            'edit' => Pages\EditSubcontractOrder::route('/{record}/edit'),
        ];
    }
}

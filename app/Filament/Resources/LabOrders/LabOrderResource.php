<?php

namespace App\Filament\Resources\LabOrders;

use App\Filament\Resources\LabOrders\Pages\CreateLabOrder;
use App\Filament\Resources\LabOrders\Pages\EditLabOrder;
use App\Filament\Resources\LabOrders\Pages\ListLabOrders;
use App\Filament\Resources\LabOrders\Schemas\LabOrderForm;
use App\Filament\Resources\LabOrders\Tables\LabOrderTable;
use App\Models\LabOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Concerns\HasPermissionAccess;

class LabOrderResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = LabOrder::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Kesehatan';
    }

    protected static ?string $label = 'Order Lab';

    protected static ?string $pluralLabel = 'Order Lab';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?int $navigationSort = 1006;

    protected static ?string $recordTitleAttribute = 'order_date';

    public static function form(Schema $schema): Schema
    {
        return LabOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabOrderTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLabOrders::route('/'),
            'create' => CreateLabOrder::route('/create'),
            'edit' => EditLabOrder::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

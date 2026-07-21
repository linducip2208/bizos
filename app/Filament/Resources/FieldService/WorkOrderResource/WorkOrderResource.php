<?php

namespace App\Filament\Resources\FieldService\WorkOrderResource;

use App\Filament\Resources\FieldService\WorkOrderResource\Pages\ListWorkOrders;
use App\Filament\Resources\FieldService\WorkOrderResource\Pages\CreateWorkOrder;
use App\Filament\Resources\FieldService\WorkOrderResource\Pages\EditWorkOrder;
use App\Filament\Resources\FieldService\WorkOrderResource\Pages\ViewWorkOrder;
use App\Filament\Resources\FieldService\WorkOrderResource\Schemas\WorkOrderForm;
use App\Filament\Resources\FieldService\WorkOrderResource\Tables\WorkOrderTable;
use App\Models\WorkOrder;
use App\Filament\Concerns\HasPermissionAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class WorkOrderResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = WorkOrder::class;

    public static function getNavigationGroup(): ?string
    {
        return '?? Industri';
    }

    protected static ?string $label = 'Work Order';

    protected static ?string $pluralLabel = 'Work Order';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return WorkOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkOrderTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkOrders::route('/'),
            'create' => CreateWorkOrder::route('/create'),
            'edit' => EditWorkOrder::route('/{record}/edit'),
            'view' => ViewWorkOrder::route('/{record}'),
        ];
    }
}
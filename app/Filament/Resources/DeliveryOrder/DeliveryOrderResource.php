<?php

namespace App\Filament\Resources\DeliveryOrder;

use App\Filament\Resources\DeliveryOrder\Pages\CreateDeliveryOrder;
use App\Filament\Resources\DeliveryOrder\Pages\EditDeliveryOrder;
use App\Filament\Resources\DeliveryOrder\Pages\ListDeliveryOrders;
use App\Filament\Resources\DeliveryOrder\Schemas\DeliveryOrderForm;
use App\Filament\Resources\DeliveryOrder\Tables\DeliveryOrderTable;
use App\Models\DeliveryOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;

class DeliveryOrderResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = DeliveryOrder::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Logistik';
    }

    protected static ?string $label = 'Surat Jalan';

    protected static ?string $pluralLabel = 'Surat Jalan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'do_number';

    public static function form(Schema $schema): Schema
    {
        return DeliveryOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryOrderTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryOrders::route('/'),
            'create' => CreateDeliveryOrder::route('/create'),
            'edit' => EditDeliveryOrder::route('/{record}/edit'),
        ];
    }
}
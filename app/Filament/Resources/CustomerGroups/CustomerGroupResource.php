<?php

namespace App\Filament\Resources\CustomerGroups;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\CustomerGroups\Pages\CreateCustomerGroup;
use App\Filament\Resources\CustomerGroups\Pages\EditCustomerGroup;
use App\Filament\Resources\CustomerGroups\Pages\ListCustomerGroups;
use App\Filament\Resources\CustomerGroups\Schemas\CustomerGroupForm;
use App\Filament\Resources\CustomerGroups\Tables\CustomerGroupsTable;
use App\Models\CustomerGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerGroupResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = CustomerGroup::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::SALES->value;
    }

    protected static ?string $label = 'Customer Groups';

    protected static ?string $pluralLabel = 'Customer Groups';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 145;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CustomerGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerGroups::route('/'),
            'create' => CreateCustomerGroup::route('/create'),
            'edit' => EditCustomerGroup::route('/{record}/edit'),
        ];
    }
}

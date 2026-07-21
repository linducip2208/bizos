<?php

namespace App\Filament\Resources\CommissionSlabs;

use App\Filament\Resources\CommissionSlabs\Pages\CreateCommissionSlab;
use App\Filament\Resources\CommissionSlabs\Pages\EditCommissionSlab;
use App\Filament\Resources\CommissionSlabs\Pages\ListCommissionSlabs;
use App\Filament\Resources\CommissionSlabs\Schemas\CommissionSlabForm;
use App\Filament\Resources\CommissionSlabs\Tables\CommissionSlabTable;
use App\Models\CommissionSlab;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class CommissionSlabResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = CommissionSlab::class;

    public static function getNavigationGroup(): string|null
    {
        return 'CRM';
    }

    protected static ?string $label = 'Komisi Slab';

    protected static ?string $pluralLabel = 'Komisi Slab';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 414;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return CommissionSlabForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommissionSlabTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommissionSlabs::route('/'),
            'create' => CreateCommissionSlab::route('/create'),
            'edit' => EditCommissionSlab::route('/{record}/edit'),
        ];
    }
}
<?php

namespace App\Filament\Resources\BankFacilityResource;

use App\Filament\Resources\BankFacilityResource\Pages\CreateBankFacility;
use App\Filament\Resources\BankFacilityResource\Pages\EditBankFacility;
use App\Filament\Resources\BankFacilityResource\Pages\ListBankFacilities;
use App\Filament\Resources\BankFacilityResource\Schemas\BankFacilityForm;
use App\Filament\Resources\BankFacilityResource\Tables\BankFacilitiesTable;
use App\Filament\Concerns\HasPermissionAccess;
use App\Models\BankFacility;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Panel;

class BankFacilityResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = BankFacility::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'bank-facilities';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Treasury';
    }

    protected static ?string $label = 'Fasilitas Bank';

    protected static ?string $pluralLabel = 'Fasilitas Bank';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 1702;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BankFacilityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankFacilitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBankFacilities::route('/'),
            'create' => CreateBankFacility::route('/create'),
            'edit' => EditBankFacility::route('/{record}/edit'),
        ];
    }
}

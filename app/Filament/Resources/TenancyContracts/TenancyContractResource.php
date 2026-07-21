<?php

namespace App\Filament\Resources\TenancyContracts;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\TenancyContracts\Pages\CreateTenancyContract;
use App\Filament\Resources\TenancyContracts\Pages\EditTenancyContract;
use App\Filament\Resources\TenancyContracts\Pages\ListTenancyContracts;
use App\Filament\Resources\TenancyContracts\Schemas\TenancyContractForm;
use App\Filament\Resources\TenancyContracts\Tables\TenancyContractTable;
use App\Models\TenancyContract;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TenancyContractResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = TenancyContract::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏠 Properti';
    }

    protected static ?string $label = 'Kontrak Sewa';
    protected static ?string $pluralLabel = 'Kontrak Sewa';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 802;

    protected static ?string $recordTitleAttribute = 'contract_number';

    public static function form(Schema $schema): Schema
    {
        return TenancyContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenancyContractTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenancyContracts::route('/'),
            'create' => CreateTenancyContract::route('/create'),
            'edit' => EditTenancyContract::route('/{record}/edit'),
        ];
    }
}
<?php

namespace App\Filament\Resources\TaxConfig;

use App\Filament\Resources\TaxConfig\Pages\CreateTaxConfig;
use App\Filament\Resources\TaxConfig\Pages\EditTaxConfig;
use App\Filament\Resources\TaxConfig\Pages\ListTaxConfigs;
use App\Filament\Resources\TaxConfig\Schemas\TaxConfigForm;
use App\Filament\Resources\TaxConfig\Tables\TaxConfigsTable;
use App\Models\TaxConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class TaxConfigResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = TaxConfig::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'tax-configs';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? Finance & Accounting';
    }

    protected static ?string $label = 'Konfigurasi Pajak';

    protected static ?string $pluralLabel = 'Konfigurasi Pajak';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?int $navigationSort = 304;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TaxConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxConfigsTable::configure($table);
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
            'index' => ListTaxConfigs::route('/'),
            'create' => CreateTaxConfig::route('/create'),
            'edit' => EditTaxConfig::route('/{record}/edit'),
        ];
    }
}
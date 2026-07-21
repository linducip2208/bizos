<?php

namespace App\Filament\Resources\LoyaltyConfigs;

use App\Filament\Resources\LoyaltyConfigs\Pages\CreateLoyaltyConfig;
use App\Filament\Resources\LoyaltyConfigs\Pages\EditLoyaltyConfig;
use App\Filament\Resources\LoyaltyConfigs\Pages\ListLoyaltyConfigs;
use App\Filament\Resources\LoyaltyConfigs\Schemas\LoyaltyConfigForm;
use App\Filament\Resources\LoyaltyConfigs\Tables\LoyaltyConfigTable;
use App\Models\LoyaltyConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class LoyaltyConfigResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = LoyaltyConfig::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? POS & Retail';
    }

    protected static ?string $label = 'Konfigurasi Loyalitas';

    protected static ?string $pluralLabel = 'Konfigurasi Loyalitas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?int $navigationSort = 612;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return LoyaltyConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoyaltyConfigTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoyaltyConfigs::route('/'),
            'create' => CreateLoyaltyConfig::route('/create'),
            'edit' => EditLoyaltyConfig::route('/{record}/edit'),
        ];
    }
}
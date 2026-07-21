<?php

namespace App\Filament\Resources\SodRules;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\SodRules\Pages\CreateSodRule;
use App\Filament\Resources\SodRules\Pages\EditSodRule;
use App\Filament\Resources\SodRules\Pages\ListSodRules;
use App\Filament\Resources\SodRules\Schemas\SodRuleForm;
use App\Filament\Resources\SodRules\Tables\SodRuleTable;
use App\Models\SodRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SodRuleResource extends Resource
{
    protected static ?string $model = SodRule::class;

    protected static ?string $label = 'Aturan SoD';

    protected static ?string $pluralLabel = 'Aturan SoD';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static ?int $navigationSort = 8;

    public static function getNavigationGroup(): string|null
    {
        return '🛡️ Compliance';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSodRules::route('/'),
            'create' => CreateSodRule::route('/create'),
            'edit' => EditSodRule::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return SodRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SodRuleTable::configure($table);
    }
}
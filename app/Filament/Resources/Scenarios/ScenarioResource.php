<?php

namespace App\Filament\Resources\Scenarios;

use App\Filament\Resources\Scenarios\Pages\CreateScenario;
use App\Filament\Resources\Scenarios\Pages\EditScenario;
use App\Filament\Resources\Scenarios\Pages\ListScenarios;
use App\Filament\Resources\Scenarios\Schemas\ScenarioForm;
use App\Filament\Resources\Scenarios\Tables\ScenariosTable;
use App\Models\Scenario;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;

class ScenarioResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Scenario::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::FINANCE->value;
    }

    protected static ?string $label = 'Scenarios';

    protected static ?string $pluralLabel = 'Scenarios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?int $navigationSort = 329;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ScenarioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScenariosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScenarios::route('/'),
            'create' => CreateScenario::route('/create'),
            'edit' => EditScenario::route('/{record}/edit'),
        ];
    }
}

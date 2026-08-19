<?php

namespace App\Filament\Resources\Objectives;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\Objectives\Pages\CreateObjective;
use App\Filament\Resources\Objectives\Pages\EditObjective;
use App\Filament\Resources\Objectives\Pages\ListObjectives;
use App\Filament\Resources\Objectives\Schemas\ObjectiveForm;
use App\Filament\Resources\Objectives\Tables\ObjectivesTable;
use App\Models\Objective;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ObjectiveResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Objective::class;

    protected static ?string $slug = 'objectives';

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::HUMAN_CAPITAL->value;
    }

    protected static ?string $label = 'Objectives (OKR)';

    protected static ?string $pluralLabel = 'OKR';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?int $navigationSort = 132;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ObjectiveForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ObjectivesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListObjectives::route('/'),
            'create' => CreateObjective::route('/create'),
            'edit' => EditObjective::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Sprints;

use App\Filament\Resources\Sprints\Pages\CreateSprint;
use App\Filament\Resources\Sprints\Pages\EditSprint;
use App\Filament\Resources\Sprints\Pages\ListSprints;
use App\Filament\Resources\Sprints\Schemas\SprintForm;
use App\Filament\Resources\Sprints\Tables\SprintTable;
use App\Models\Sprint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class SprintResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Sprint::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Project';
    }

    protected static ?string $label = 'Sprint';

    protected static ?string $pluralLabel = 'Sprint';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?int $navigationSort = 511;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SprintForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SprintTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSprints::route('/'),
            'create' => CreateSprint::route('/create'),
            'edit' => EditSprint::route('/{record}/edit'),
        ];
    }
}

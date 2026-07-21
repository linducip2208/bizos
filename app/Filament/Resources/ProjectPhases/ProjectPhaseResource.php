<?php

namespace App\Filament\Resources\ProjectPhases;

use App\Filament\Resources\ProjectPhases\Pages\CreateProjectPhase;
use App\Filament\Resources\ProjectPhases\Pages\EditProjectPhase;
use App\Filament\Resources\ProjectPhases\Pages\ListProjectPhases;
use App\Filament\Resources\ProjectPhases\Schemas\ProjectPhaseForm;
use App\Filament\Resources\ProjectPhases\Tables\ProjectPhasesTable;
use App\Models\ProjectPhase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class ProjectPhaseResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = ProjectPhase::class;

    public static function getNavigationGroup(): string|null
    {
        return '📋 Project Management';
    }

    protected static ?string $label = 'Fase Project';

    protected static ?string $pluralLabel = 'Fase Project';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquaresPlus;

    protected static ?int $navigationSort = 506;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProjectPhaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectPhasesTable::configure($table);
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
            'index' => ListProjectPhases::route('/'),
            'create' => CreateProjectPhase::route('/create'),
            'edit' => EditProjectPhase::route('/{record}/edit'),
        ];
    }
}
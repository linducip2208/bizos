<?php

namespace App\Filament\Resources\ThreeWayMatches;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ThreeWayMatches\Pages\CreateThreeWayMatch;
use App\Filament\Resources\ThreeWayMatches\Pages\EditThreeWayMatch;
use App\Filament\Resources\ThreeWayMatches\Pages\ListThreeWayMatches;
use App\Filament\Resources\ThreeWayMatches\Schemas\ThreeWayMatchForm;
use App\Filament\Resources\ThreeWayMatches\Tables\ThreeWayMatchesTable;
use App\Models\ThreeWayMatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ThreeWayMatchResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ThreeWayMatch::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Procurement';
    }

    protected static ?string $label = '3-Way Matching';

    protected static ?string $pluralLabel = '3-Way Matching';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static ?int $navigationSort = 115;

    public static function form(Schema $schema): Schema
    {
        return ThreeWayMatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ThreeWayMatchesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListThreeWayMatches::route('/'),
            'create' => CreateThreeWayMatch::route('/create'),
            'edit' => EditThreeWayMatch::route('/{record}/edit'),
        ];
    }
}

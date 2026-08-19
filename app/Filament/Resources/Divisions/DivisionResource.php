<?php

namespace App\Filament\Resources\Divisions;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\Divisions\Pages\CreateDivision;
use App\Filament\Resources\Divisions\Pages\EditDivision;
use App\Filament\Resources\Divisions\Pages\ListDivisions;
use App\Filament\Resources\Divisions\Schemas\DivisionForm;
use App\Filament\Resources\Divisions\Tables\DivisionsTable;
use App\Models\Division;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DivisionResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Division::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Organization';
    }

    protected static ?string $label = 'Divisions';

    protected static ?string $pluralLabel = 'Divisions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DivisionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DivisionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDivisions::route('/'),
            'create' => CreateDivision::route('/create'),
            'edit' => EditDivision::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\ProjectSiteInventories;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ProjectSiteInventories\Pages\CreateProjectSiteInventory;
use App\Filament\Resources\ProjectSiteInventories\Pages\EditProjectSiteInventory;
use App\Filament\Resources\ProjectSiteInventories\Pages\ListProjectSiteInventories;
use App\Filament\Resources\ProjectSiteInventories\Schemas\ProjectSiteInventoryForm;
use App\Filament\Resources\ProjectSiteInventories\Tables\ProjectSiteInventoryTable;
use App\Models\ProjectSiteInventory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProjectSiteInventoryResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ProjectSiteInventory::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏗️ Konstruksi';
    }

    protected static ?string $label = 'Inventaris Site';
    protected static ?string $pluralLabel = 'Inventaris Site';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = 603;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ProjectSiteInventoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectSiteInventoryTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjectSiteInventories::route('/'),
            'create' => CreateProjectSiteInventory::route('/create'),
            'edit' => EditProjectSiteInventory::route('/{record}/edit'),
        ];
    }
}

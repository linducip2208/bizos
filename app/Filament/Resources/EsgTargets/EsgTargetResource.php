<?php

namespace App\Filament\Resources\EsgTargets;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\EsgTargets\Pages\CreateEsgTarget;
use App\Filament\Resources\EsgTargets\Pages\EditEsgTarget;
use App\Filament\Resources\EsgTargets\Pages\ListEsgTargets;
use App\Filament\Resources\EsgTargets\Schemas\EsgTargetForm;
use App\Filament\Resources\EsgTargets\Tables\EsgTargetsTable;
use App\Models\EsgTarget;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EsgTargetResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = EsgTarget::class;

    public static function getNavigationGroup(): string|null
    {
        return '🌱 ESG & Sustainability';
    }

    protected static ?string $label = 'Target';

    protected static ?string $pluralLabel = 'Target ESG';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return EsgTargetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EsgTargetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEsgTargets::route('/'),
            'create' => CreateEsgTarget::route('/create'),
            'edit' => EditEsgTarget::route('/{record}/edit'),
        ];
    }
}
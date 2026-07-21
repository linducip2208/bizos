<?php

namespace App\Filament\Resources\GamificationBadgeResource;

use App\Filament\Resources\GamificationBadgeResource\Pages\CreateBadge;
use App\Filament\Resources\GamificationBadgeResource\Pages\EditBadge;
use App\Filament\Resources\GamificationBadgeResource\Pages\ListBadges;
use App\Filament\Resources\GamificationBadgeResource\Schemas\BadgeForm;
use App\Filament\Resources\GamificationBadgeResource\Tables\BadgeTable;
use App\Models\GamificationBadge;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class GamificationBadgeResource extends Resource
{
    protected static ?string $model = GamificationBadge::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Gamifikasi';
    }

    protected static ?string $label = 'Badge';

    protected static ?string $pluralLabel = 'Badge';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BadgeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BadgeTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBadges::route('/'),
            'create' => CreateBadge::route('/create'),
            'edit' => EditBadge::route('/{record}/edit'),
        ];
    }
}

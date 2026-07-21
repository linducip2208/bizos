<?php

namespace App\Filament\Resources\FinishedGoods;

use App\Filament\Resources\FinishedGoods\Pages;
use App\Models\FinishedGood;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class FinishedGoodResource extends Resource
{
    use HasPermissionAccess;

    // Gunakan Product dengan product_type = 'finished' — FinishedGood = Product.
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = FinishedGood::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏭 Industry';
    }

    protected static ?string $label = 'Barang Jadi';

    protected static ?string $pluralLabel = 'Barang Jadi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return Schemas\FinishedGoodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\FinishedGoodTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinishedGoods::route('/'),
            'create' => Pages\CreateFinishedGood::route('/create'),
            'edit' => Pages\EditFinishedGood::route('/{record}/edit'),
        ];
    }
}

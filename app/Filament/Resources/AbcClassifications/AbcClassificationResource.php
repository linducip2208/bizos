<?php

namespace App\Filament\Resources\AbcClassifications;

use App\Filament\Resources\AbcClassifications\Pages\ListAbcClassifications;
use App\Filament\Resources\AbcClassifications\Tables\AbcClassificationTable;
use App\Models\AbcClassification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class AbcClassificationResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = AbcClassification::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Procurement & Inventory';
    }

    protected static ?string $label = 'Klasifikasi ABC';

    protected static ?string $pluralLabel = 'Klasifikasi ABC';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?int $navigationSort = 114;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return AbcClassificationTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAbcClassifications::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
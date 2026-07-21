<?php

namespace App\Filament\Resources\Batches;

use App\Filament\Resources\Batches\Pages;
use App\Models\Batch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class BatchResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Batch::class;

    public static function getNavigationGroup(): string|null
    {
        return '📦 Inventori';
    }

    protected static ?string $label = 'Batch';

    protected static ?string $pluralLabel = 'Batch';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'batch_number';

    public static function form(Schema $schema): Schema
    {
        return Schemas\BatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\BatchTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBatches::route('/'),
            'create' => Pages\CreateBatch::route('/create'),
            'edit' => Pages\EditBatch::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\RoutingOperations;

use App\Filament\Resources\RoutingOperations\Pages;
use App\Models\RoutingOperation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class RoutingOperationResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = RoutingOperation::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? Industri';
    }

    protected static ?string $label = 'Routing Operasi';

    protected static ?string $pluralLabel = 'Routing Operasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'operation_name';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\RoutingOperations\Schemas\RoutingOperationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\RoutingOperations\Tables\RoutingOperationTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoutingOperations::route('/'),
            'create' => Pages\CreateRoutingOperation::route('/create'),
            'edit' => Pages\EditRoutingOperation::route('/{record}/edit'),
        ];
    }
}
<?php

namespace App\Filament\Resources\WorkCenters;

use App\Filament\Resources\WorkCenters\Pages;
use App\Models\WorkCenter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class WorkCenterResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = WorkCenter::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Manufaktur';
    }

    protected static ?string $label = 'Work Center';

    protected static ?string $pluralLabel = 'Work Center';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\WorkCenters\Schemas\WorkCenterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\WorkCenters\Tables\WorkCenterTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkCenters::route('/'),
            'create' => Pages\CreateWorkCenter::route('/create'),
            'edit' => Pages\EditWorkCenter::route('/{record}/edit'),
        ];
    }
}
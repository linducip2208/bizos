<?php

namespace App\Filament\Resources\DashboardLayouts;

use App\Filament\Resources\DashboardLayouts\Pages\CreateDashboardLayout;
use App\Filament\Resources\DashboardLayouts\Pages\EditDashboardLayout;
use App\Filament\Resources\DashboardLayouts\Pages\ListDashboardLayouts;
use App\Filament\Resources\DashboardLayouts\Schemas\DashboardLayoutForm;
use App\Filament\Resources\DashboardLayouts\Tables\DashboardLayoutsTable;
use App\Models\DashboardLayout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DashboardLayoutResource extends Resource
{
    protected static ?string $model = DashboardLayout::class;

    protected static ?string $label = 'Dashboard Layout';

    protected static ?string $pluralLabel = 'Dashboard Layout';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?int $navigationSort = 1112;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan';
    }

    public static function form(Schema $schema): Schema
    {
        return DashboardLayoutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DashboardLayoutsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDashboardLayouts::route('/'),
            'create' => CreateDashboardLayout::route('/create'),
            'edit' => EditDashboardLayout::route('/{record}/edit'),
        ];
    }
}
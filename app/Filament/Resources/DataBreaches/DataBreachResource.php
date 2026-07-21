<?php

namespace App\Filament\Resources\DataBreaches;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\DataBreaches\Pages\CreateDataBreach;
use App\Filament\Resources\DataBreaches\Pages\EditDataBreach;
use App\Filament\Resources\DataBreaches\Pages\ListDataBreaches;
use App\Filament\Resources\DataBreaches\Pages\ViewDataBreach;
use App\Filament\Resources\DataBreaches\Schemas\DataBreachForm;
use App\Filament\Resources\DataBreaches\Tables\DataBreachTable;
use App\Models\DataBreach;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DataBreachResource extends Resource
{
    protected static ?string $model = DataBreach::class;

    protected static ?string $label = 'Pelanggaran Data';

    protected static ?string $pluralLabel = 'Pelanggaran Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string|null
    {
        return '🛡️ Compliance';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDataBreaches::route('/'),
            'create' => CreateDataBreach::route('/create'),
            'edit' => EditDataBreach::route('/{record}/edit'),
            'view' => ViewDataBreach::route('/{record}'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return DataBreachForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataBreachTable::configure($table);
    }
}
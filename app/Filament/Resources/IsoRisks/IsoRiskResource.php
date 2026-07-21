<?php

namespace App\Filament\Resources\IsoRisks;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\IsoRisks\Pages\CreateIsoRisk;
use App\Filament\Resources\IsoRisks\Pages\EditIsoRisk;
use App\Filament\Resources\IsoRisks\Pages\ListIsoRisks;
use App\Filament\Resources\IsoRisks\Schemas\IsoRiskForm;
use App\Filament\Resources\IsoRisks\Tables\IsoRiskTable;
use App\Models\IsoRisk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IsoRiskResource extends Resource
{
    protected static ?string $model = IsoRisk::class;

    protected static ?string $label = 'Risiko ISO';

    protected static ?string $pluralLabel = 'Risiko ISO';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string|null
    {
        return '🛡️ Compliance';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIsoRisks::route('/'),
            'create' => CreateIsoRisk::route('/create'),
            'edit' => EditIsoRisk::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return IsoRiskForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IsoRiskTable::configure($table);
    }
}
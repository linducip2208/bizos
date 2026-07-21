<?php

namespace App\Filament\Resources\ThrConfig;

use App\Filament\Resources\ThrConfig\Pages\CreateThrConfig;
use App\Filament\Resources\ThrConfig\Pages\EditThrConfig;
use App\Filament\Resources\ThrConfig\Pages\ListThrConfigs;
use App\Filament\Resources\ThrConfig\Schemas\ThrConfigForm;
use App\Filament\Resources\ThrConfig\Tables\ThrConfigsTable;
use App\Models\ThrConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class ThrConfigResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = ThrConfig::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'thr-configs';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? HR & Payroll';
    }

    protected static ?string $label = 'Konfigurasi THR';

    protected static ?string $pluralLabel = 'Konfigurasi THR';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?int $navigationSort = 209;

    protected static ?string $recordTitleAttribute = 'religious_holiday';

    public static function form(Schema $schema): Schema
    {
        return ThrConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ThrConfigsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListThrConfigs::route('/'),
            'create' => CreateThrConfig::route('/create'),
            'edit' => EditThrConfig::route('/{record}/edit'),
        ];
    }
}
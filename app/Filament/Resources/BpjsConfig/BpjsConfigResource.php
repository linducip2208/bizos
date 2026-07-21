<?php

namespace App\Filament\Resources\BpjsConfig;

use App\Filament\Resources\BpjsConfig\Pages\CreateBpjsConfig;
use App\Filament\Resources\BpjsConfig\Pages\EditBpjsConfig;
use App\Filament\Resources\BpjsConfig\Pages\ListBpjsConfigs;
use App\Filament\Resources\BpjsConfig\Schemas\BpjsConfigForm;
use App\Filament\Resources\BpjsConfig\Tables\BpjsConfigsTable;
use App\Models\BpjsConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class BpjsConfigResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = BpjsConfig::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'bpjs-configs';
    }

    public static function getNavigationGroup(): string|null
    {
        return '💰 Payroll';
    }

    protected static ?string $label = 'Konfigurasi BPJS';

    protected static ?string $pluralLabel = 'Konfigurasi BPJS';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?int $navigationSort = 208;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return BpjsConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BpjsConfigsTable::configure($table);
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
            'index' => ListBpjsConfigs::route('/'),
            'create' => CreateBpjsConfig::route('/create'),
            'edit' => EditBpjsConfig::route('/{record}/edit'),
        ];
    }
}
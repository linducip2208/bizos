<?php

namespace App\Filament\Resources\Pph21Config;

use App\Filament\Resources\Pph21Config\Pages\CreatePph21Config;
use App\Filament\Resources\Pph21Config\Pages\EditPph21Config;
use App\Filament\Resources\Pph21Config\Pages\ListPph21Configs;
use App\Filament\Resources\Pph21Config\Schemas\Pph21ConfigForm;
use App\Filament\Resources\Pph21Config\Tables\Pph21ConfigsTable;
use App\Models\Pph21Config;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class Pph21ConfigResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = Pph21Config::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'pph21-configs';
    }

    public static function getNavigationGroup(): string|null
    {
        return '💰 Payroll';
    }

    protected static ?string $label = 'Konfigurasi PPh 21';

    protected static ?string $pluralLabel = 'Konfigurasi PPh 21';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?int $navigationSort = 207;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return Pph21ConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Pph21ConfigsTable::configure($table);
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
            'index' => ListPph21Configs::route('/'),
            'create' => CreatePph21Config::route('/create'),
            'edit' => EditPph21Config::route('/{record}/edit'),
        ];
    }
}
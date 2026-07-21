<?php

namespace App\Filament\Resources\FieldService\TechnicianVanResource;

use App\Filament\Resources\FieldService\TechnicianVanResource\Pages\ListTechnicianVans;
use App\Filament\Resources\FieldService\TechnicianVanResource\Pages\CreateTechnicianVan;
use App\Filament\Resources\FieldService\TechnicianVanResource\Pages\EditTechnicianVan;
use App\Filament\Resources\FieldService\TechnicianVanResource\Schemas\TechnicianVanForm;
use App\Filament\Resources\FieldService\TechnicianVanResource\Tables\TechnicianVanTable;
use App\Models\TechnicianVan;
use App\Filament\Concerns\HasPermissionAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TechnicianVanResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = TechnicianVan::class;

    public static function getNavigationGroup(): ?string
    {
        return '?? Industri';
    }

    protected static ?string $label = 'Van Teknisi';

    protected static ?string $pluralLabel = 'Van Teknisi';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return TechnicianVanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TechnicianVanTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTechnicianVans::route('/'),
            'create' => CreateTechnicianVan::route('/create'),
            'edit' => EditTechnicianVan::route('/{record}/edit'),
        ];
    }
}
<?php

namespace App\Filament\Resources\Coa;

use App\Filament\Resources\Coa\Pages\CreateCoa;
use App\Filament\Resources\Coa\Pages\EditCoa;
use App\Filament\Resources\Coa\Pages\ListCoas;
use App\Filament\Resources\Coa\Schemas\CoaForm;
use App\Filament\Resources\Coa\Tables\CoasTable;
use App\Models\Coa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class CoaResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = Coa::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'coas';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? Finance & Accounting';
    }

    protected static ?string $label = 'COA';

    protected static ?string $pluralLabel = 'COA';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 302;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CoaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoasTable::configure($table);
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
            'index' => ListCoas::route('/'),
            'create' => CreateCoa::route('/create'),
            'edit' => EditCoa::route('/{record}/edit'),
        ];
    }
}
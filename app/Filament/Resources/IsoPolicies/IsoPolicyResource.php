<?php

namespace App\Filament\Resources\IsoPolicies;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\IsoPolicies\Pages\CreateIsoPolicy;
use App\Filament\Resources\IsoPolicies\Pages\EditIsoPolicy;
use App\Filament\Resources\IsoPolicies\Pages\ListIsoPolicies;
use App\Filament\Resources\IsoPolicies\Schemas\IsoPolicyForm;
use App\Filament\Resources\IsoPolicies\Tables\IsoPolicyTable;
use App\Models\IsoPolicy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IsoPolicyResource extends Resource
{
    protected static ?string $model = IsoPolicy::class;

    protected static ?string $label = 'Kebijakan ISO';

    protected static ?string $pluralLabel = 'Kebijakan ISO';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): string|null
    {
        return 'Kepatuhan';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIsoPolicies::route('/'),
            'create' => CreateIsoPolicy::route('/create'),
            'edit' => EditIsoPolicy::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return IsoPolicyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IsoPolicyTable::configure($table);
    }
}

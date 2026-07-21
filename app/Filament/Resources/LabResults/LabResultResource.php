<?php

namespace App\Filament\Resources\LabResults;

use App\Filament\Resources\LabResults\Pages\CreateLabResult;
use App\Filament\Resources\LabResults\Pages\EditLabResult;
use App\Filament\Resources\LabResults\Pages\ListLabResults;
use App\Filament\Resources\LabResults\Schemas\LabResultForm;
use App\Filament\Resources\LabResults\Tables\LabResultTable;
use App\Models\LabResult;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Concerns\HasPermissionAccess;

class LabResultResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = LabResult::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Kesehatan';
    }

    protected static ?string $label = 'Hasil Lab';

    protected static ?string $pluralLabel = 'Hasil Lab';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?int $navigationSort = 1007;

    protected static ?string $recordTitleAttribute = 'test_name';

    public static function form(Schema $schema): Schema
    {
        return LabResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabResultTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLabResults::route('/'),
            'create' => CreateLabResult::route('/create'),
            'edit' => EditLabResult::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
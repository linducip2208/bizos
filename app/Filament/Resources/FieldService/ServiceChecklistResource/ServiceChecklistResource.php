<?php

namespace App\Filament\Resources\FieldService\ServiceChecklistResource;

use App\Filament\Resources\FieldService\ServiceChecklistResource\Pages\ListServiceChecklists;
use App\Filament\Resources\FieldService\ServiceChecklistResource\Pages\CreateServiceChecklist;
use App\Filament\Resources\FieldService\ServiceChecklistResource\Pages\EditServiceChecklist;
use App\Filament\Resources\FieldService\ServiceChecklistResource\Schemas\ServiceChecklistForm;
use App\Filament\Resources\FieldService\ServiceChecklistResource\Tables\ServiceChecklistTable;
use App\Models\ServiceChecklist;
use App\Filament\Concerns\HasPermissionAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ServiceChecklistResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ServiceChecklist::class;

    public static function getNavigationGroup(): ?string
    {
        return 'Field Service';
    }

    protected static ?string $label = 'Checklist';

    protected static ?string $pluralLabel = 'Checklist';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-circle';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return ServiceChecklistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceChecklistTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceChecklists::route('/'),
            'create' => CreateServiceChecklist::route('/create'),
            'edit' => EditServiceChecklist::route('/{record}/edit'),
        ];
    }
}

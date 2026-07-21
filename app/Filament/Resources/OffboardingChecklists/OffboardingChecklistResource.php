<?php

namespace App\Filament\Resources\OffboardingChecklists;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\OffboardingChecklists\Pages\CreateOffboardingChecklist;
use App\Filament\Resources\OffboardingChecklists\Pages\EditOffboardingChecklist;
use App\Filament\Resources\OffboardingChecklists\Pages\ListOffboardingChecklists;
use App\Filament\Resources\OffboardingChecklists\Schemas\OffboardingChecklistForm;
use App\Filament\Resources\OffboardingChecklists\Tables\OffboardingChecklistsTable;
use App\Models\OffboardingChecklist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OffboardingChecklistResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = OffboardingChecklist::class;

    public static function getNavigationGroup(): string|null
    {
        return '👥 Human Capital';
    }

    protected static ?string $label = 'Checklist Offboarding';
    protected static ?string $pluralLabel = 'Checklist Offboarding';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static ?int $navigationSort = 125;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OffboardingChecklistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OffboardingChecklistsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOffboardingChecklists::route('/'),
            'create' => CreateOffboardingChecklist::route('/create'),
            'edit' => EditOffboardingChecklist::route('/{record}/edit'),
        ];
    }
}
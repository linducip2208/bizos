<?php

namespace App\Filament\Resources\OnboardingChecklists;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\OnboardingChecklists\Pages\CreateOnboardingChecklist;
use App\Filament\Resources\OnboardingChecklists\Pages\EditOnboardingChecklist;
use App\Filament\Resources\OnboardingChecklists\Pages\ListOnboardingChecklists;
use App\Filament\Resources\OnboardingChecklists\Schemas\OnboardingChecklistForm;
use App\Filament\Resources\OnboardingChecklists\Tables\OnboardingChecklistsTable;
use App\Models\OnboardingChecklist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OnboardingChecklistResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = OnboardingChecklist::class;

    public static function getNavigationGroup(): string|null
    {
        return 'HRM';
    }

    protected static ?string $label = 'Checklist Onboarding';
    protected static ?string $pluralLabel = 'Checklist Onboarding';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static ?int $navigationSort = 124;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OnboardingChecklistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OnboardingChecklistsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOnboardingChecklists::route('/'),
            'create' => CreateOnboardingChecklist::route('/create'),
            'edit' => EditOnboardingChecklist::route('/{record}/edit'),
        ];
    }
}

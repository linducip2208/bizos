<?php

namespace App\Filament\Resources\LeadActivities;

use App\Filament\Resources\LeadActivities\Pages\CreateLeadActivity;
use App\Filament\Resources\LeadActivities\Pages\EditLeadActivity;
use App\Filament\Resources\LeadActivities\Pages\ListLeadActivities;
use App\Filament\Resources\LeadActivities\Schemas\LeadActivityForm;
use App\Filament\Resources\LeadActivities\Tables\LeadActivitiesTable;
use App\Models\LeadActivity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class LeadActivityResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = LeadActivity::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::SALES->value;
    }

    protected static ?string $label = 'Lead Activities';

    protected static ?string $pluralLabel = 'Lead Activities';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTopRightOnSquare;

    protected static ?int $navigationSort = 409;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return LeadActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadActivitiesTable::configure($table);
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
            'index' => ListLeadActivities::route('/'),
            'create' => CreateLeadActivity::route('/create'),
            'edit' => EditLeadActivity::route('/{record}/edit'),
        ];
    }
}
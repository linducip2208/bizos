<?php

namespace App\Filament\Resources\ActivityTimeline;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ActivityTimeline\Pages\ListActivityTimeline;
use App\Filament\Resources\ActivityTimeline\Tables\ActivityTimelineTable;
use App\Models\ActivityTimeline as ActivityTimelineModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActivityTimelineResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ActivityTimelineModel::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏠 Dashboard';
    }

    protected static ?string $label = 'Aktivitas';

    protected static ?string $pluralLabel = 'Aktivitas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'description';

    public static function table(Table $table): Table
    {
        return ActivityTimelineTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityTimeline::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}

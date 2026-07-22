<?php

namespace App\Filament\Resources\MeetingMinutes;

use App\Filament\Resources\MeetingMinutes\Pages\CreateMeetingMinute;
use App\Filament\Resources\MeetingMinutes\Pages\EditMeetingMinute;
use App\Filament\Resources\MeetingMinutes\Pages\ListMeetingMinutes;
use App\Filament\Resources\MeetingMinutes\Schemas\MeetingMinuteForm;
use App\Filament\Resources\MeetingMinutes\Tables\MeetingMinutesTable;
use App\Models\MeetingMinute;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class MeetingMinuteResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;
    protected static ?string $model = MeetingMinute::class;

    public static function getNavigationGroup(): string|null
    {
        return '💬 Collaboration';
    }

    protected static ?string $label = 'Notula Rapat';

    protected static ?string $pluralLabel = 'Notula Rapat';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 706;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return MeetingMinuteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MeetingMinutesTable::configure($table);
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
            'index' => ListMeetingMinutes::route('/'),
            'create' => CreateMeetingMinute::route('/create'),
            'edit' => EditMeetingMinute::route('/{record}/edit'),
        ];
    }
}
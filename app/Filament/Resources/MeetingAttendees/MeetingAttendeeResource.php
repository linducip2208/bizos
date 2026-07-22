<?php

namespace App\Filament\Resources\MeetingAttendees;

use App\Filament\Resources\MeetingAttendees\Pages\CreateMeetingAttendee;
use App\Filament\Resources\MeetingAttendees\Pages\EditMeetingAttendee;
use App\Filament\Resources\MeetingAttendees\Pages\ListMeetingAttendees;
use App\Filament\Resources\MeetingAttendees\Schemas\MeetingAttendeeForm;
use App\Filament\Resources\MeetingAttendees\Tables\MeetingAttendeesTable;
use App\Models\MeetingAttendee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class MeetingAttendeeResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;
    protected static ?string $model = MeetingAttendee::class;

    public static function getNavigationGroup(): string|null
    {
        return '💬 Collaboration';
    }

    protected static ?string $label = 'Peserta Rapat';

    protected static ?string $pluralLabel = 'Peserta Rapat';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 705;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return MeetingAttendeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MeetingAttendeesTable::configure($table);
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
            'index' => ListMeetingAttendees::route('/'),
            'create' => CreateMeetingAttendee::route('/create'),
            'edit' => EditMeetingAttendee::route('/{record}/edit'),
        ];
    }
}
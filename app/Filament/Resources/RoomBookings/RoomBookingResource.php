<?php

namespace App\Filament\Resources\RoomBookings;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\RoomBookings\Pages\CreateRoomBooking;
use App\Filament\Resources\RoomBookings\Pages\EditRoomBooking;
use App\Filament\Resources\RoomBookings\Pages\ListRoomBookings;
use App\Filament\Resources\RoomBookings\Schemas\RoomBookingForm;
use App\Filament\Resources\RoomBookings\Tables\RoomBookingTable;
use App\Models\RoomBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RoomBookingResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = RoomBooking::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏭 Industry';
    }

    protected static ?string $label = 'Booking';
    protected static ?string $pluralLabel = 'Booking';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?int $navigationSort = 702;

    protected static ?string $recordTitleAttribute = 'guest_name';

    public static function form(Schema $schema): Schema
    {
        return RoomBookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomBookingTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoomBookings::route('/'),
            'create' => CreateRoomBooking::route('/create'),
            'edit' => EditRoomBooking::route('/{record}/edit'),
        ];
    }
}
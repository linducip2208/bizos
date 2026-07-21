<?php

namespace App\Filament\Resources\HotelServices;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\HotelServices\Pages\CreateHotelService;
use App\Filament\Resources\HotelServices\Pages\EditHotelService;
use App\Filament\Resources\HotelServices\Pages\ListHotelServices;
use App\Filament\Resources\HotelServices\Schemas\HotelServiceForm;
use App\Filament\Resources\HotelServices\Tables\HotelServiceTable;
use App\Models\HotelService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HotelServiceResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = HotelService::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏨 Perhotelan';
    }

    protected static ?string $label = 'Layanan';
    protected static ?string $pluralLabel = 'Layanan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 703;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return HotelServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotelServiceTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHotelServices::route('/'),
            'create' => CreateHotelService::route('/create'),
            'edit' => EditHotelService::route('/{record}/edit'),
        ];
    }
}
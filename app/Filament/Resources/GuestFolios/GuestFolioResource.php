<?php

namespace App\Filament\Resources\GuestFolios;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\GuestFolios\Pages\CreateGuestFolio;
use App\Filament\Resources\GuestFolios\Pages\EditGuestFolio;
use App\Filament\Resources\GuestFolios\Pages\ListGuestFolios;
use App\Filament\Resources\GuestFolios\Schemas\GuestFolioForm;
use App\Filament\Resources\GuestFolios\Tables\GuestFolioTable;
use App\Models\GuestFolio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GuestFolioResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = GuestFolio::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Perhotelan';
    }

    protected static ?string $label = 'Folio Tamu';
    protected static ?string $pluralLabel = 'Folio Tamu';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?int $navigationSort = 704;

    protected static ?string $recordTitleAttribute = 'folio_number';

    public static function form(Schema $schema): Schema
    {
        return GuestFolioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuestFolioTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuestFolios::route('/'),
            'create' => CreateGuestFolio::route('/create'),
            'edit' => EditGuestFolio::route('/{record}/edit'),
        ];
    }
}
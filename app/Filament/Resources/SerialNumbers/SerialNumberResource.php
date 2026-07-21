<?php

namespace App\Filament\Resources\SerialNumbers;

use App\Filament\Resources\SerialNumbers\Pages;
use App\Models\SerialNumber;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class SerialNumberResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = SerialNumber::class;

    public static function getNavigationGroup(): string|null
    {
        return '📦 Inventori';
    }

    protected static ?string $label = 'Nomor Seri';

    protected static ?string $pluralLabel = 'Nomor Seri';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'serial_number';

    public static function form(Schema $schema): Schema
    {
        return Schemas\SerialNumberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\SerialNumberTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSerialNumbers::route('/'),
            'create' => Pages\CreateSerialNumber::route('/create'),
            'edit' => Pages\EditSerialNumber::route('/{record}/edit'),
        ];
    }
}

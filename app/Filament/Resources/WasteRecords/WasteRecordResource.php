<?php

namespace App\Filament\Resources\WasteRecords;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\WasteRecords\Pages\CreateWasteRecord;
use App\Filament\Resources\WasteRecords\Pages\EditWasteRecord;
use App\Filament\Resources\WasteRecords\Pages\ListWasteRecords;
use App\Filament\Resources\WasteRecords\Schemas\WasteRecordForm;
use App\Filament\Resources\WasteRecords\Tables\WasteRecordsTable;
use App\Models\WasteRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WasteRecordResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = WasteRecord::class;

    public static function getNavigationGroup(): string|null
    {
        return '??? Compliance';
    }

    protected static ?string $label = 'Limbah';

    protected static ?string $pluralLabel = 'Catatan Limbah';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrash;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return WasteRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WasteRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWasteRecords::route('/'),
            'create' => CreateWasteRecord::route('/create'),
            'edit' => EditWasteRecord::route('/{record}/edit'),
        ];
    }
}
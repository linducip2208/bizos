<?php

namespace App\Filament\Resources\ConsentRecords;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ConsentRecords\Pages\CreateConsentRecord;
use App\Filament\Resources\ConsentRecords\Pages\EditConsentRecord;
use App\Filament\Resources\ConsentRecords\Pages\ListConsentRecords;
use App\Filament\Resources\ConsentRecords\Schemas\ConsentRecordForm;
use App\Filament\Resources\ConsentRecords\Tables\ConsentRecordTable;
use App\Models\ConsentRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ConsentRecordResource extends Resource
{
    protected static ?string $model = ConsentRecord::class;

    protected static ?string $label = 'Catatan Persetujuan';

    protected static ?string $pluralLabel = 'Catatan Persetujuan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|null
    {
        return '??? Compliance';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConsentRecords::route('/'),
            'create' => CreateConsentRecord::route('/create'),
            'edit' => EditConsentRecord::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ConsentRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConsentRecordTable::configure($table);
    }
}
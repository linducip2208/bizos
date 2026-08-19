<?php

namespace App\Filament\Resources\JournalEntry;

use App\Filament\Resources\JournalEntry\Pages\CreateJournalEntry;
use App\Filament\Resources\JournalEntry\Pages\EditJournalEntry;
use App\Filament\Resources\JournalEntry\Pages\ListJournalEntries;
use App\Filament\Resources\JournalEntry\Schemas\JournalEntryForm;
use App\Filament\Resources\JournalEntry\Tables\JournalEntriesTable;
use App\Models\JournalEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class JournalEntryResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;
    protected static ?string $model = JournalEntry::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::FINANCE->value;
    }

    protected static ?string $label = 'Journal Entries';

    protected static ?string $pluralLabel = 'Journal Entries';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    protected static ?int $navigationSort = 328;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return JournalEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JournalEntriesTable::configure($table);
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
            'index' => ListJournalEntries::route('/'),
            'create' => CreateJournalEntry::route('/create'),
            'edit' => EditJournalEntry::route('/{record}/edit'),
        ];
    }
}
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
    use HasPermissionAccess;
    protected static ?string $model = JournalEntry::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'journal-entries';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance';
    }

    protected static ?string $label = 'Entri Jurnal';

    protected static ?string $pluralLabel = 'Entri Jurnal';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 308;

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

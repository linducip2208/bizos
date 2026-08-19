<?php

namespace App\Filament\Resources\IntercompanyTransactions;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\IntercompanyTransactions\Pages\CreateIntercompanyTransaction;
use App\Filament\Resources\IntercompanyTransactions\Pages\EditIntercompanyTransaction;
use App\Filament\Resources\IntercompanyTransactions\Pages\ListIntercompanyTransactions;
use App\Filament\Resources\IntercompanyTransactions\Schemas\IntercompanyTransactionForm;
use App\Filament\Resources\IntercompanyTransactions\Tables\IntercompanyTransactionTable;
use App\Models\IntercompanyTransaction;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IntercompanyTransactionResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = IntercompanyTransaction::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'Finance & Accounting';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance & Accounting';
    }

    protected static ?string $label = 'Intercompany Transactions';

    protected static ?string $pluralLabel = 'Intercompany';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlay;

    protected static ?int $navigationSort = 330;

    protected static ?string $recordTitleAttribute = 'reference_number';

    public static function form(Schema $schema): Schema
    {
        return IntercompanyTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IntercompanyTransactionTable::configure($table);
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
            'index' => ListIntercompanyTransactions::route('/'),
            'create' => CreateIntercompanyTransaction::route('/create'),
            'edit' => EditIntercompanyTransaction::route('/{record}/edit'),
        ];
    }
}

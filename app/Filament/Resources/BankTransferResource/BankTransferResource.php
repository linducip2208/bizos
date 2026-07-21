<?php

namespace App\Filament\Resources\BankTransferResource;

use App\Filament\Resources\BankTransferResource\Pages\CreateBankTransfer;
use App\Filament\Resources\BankTransferResource\Pages\EditBankTransfer;
use App\Filament\Resources\BankTransferResource\Pages\ListBankTransfers;
use App\Filament\Resources\BankTransferResource\Schemas\BankTransferForm;
use App\Filament\Resources\BankTransferResource\Tables\BankTransfersTable;
use App\Models\BankTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;
use Filament\Panel;

class BankTransferResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = BankTransfer::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'bank-transfers';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance';
    }

    protected static ?string $label = 'Transfer Bank';

    protected static ?string $pluralLabel = 'Transfer Bank';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowRightStartOnRectangle;

    protected static ?int $navigationSort = 326;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return BankTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankTransfersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBankTransfers::route('/'),
            'create' => CreateBankTransfer::route('/create'),
            'edit' => EditBankTransfer::route('/{record}/edit'),
        ];
    }
}

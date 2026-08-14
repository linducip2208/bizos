<?php

namespace App\Filament\Resources\Bids;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\Bids\Pages\CreateBid;
use App\Filament\Resources\Bids\Pages\EditBid;
use App\Filament\Resources\Bids\Pages\ListBids;
use App\Filament\Resources\Bids\Schemas\BidForm;
use App\Filament\Resources\Bids\Tables\BidTable;
use App\Models\Bid;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BidResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Bid::class;

    public static function getNavigationGroup(): string|null
    {
        return '📑 Procurement';
    }

    protected static ?string $label = 'Penawaran';

    protected static ?string $pluralLabel = 'Penawaran';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?int $navigationSort = 108;

    protected static ?string $recordTitleAttribute = 'bid_number';

    public static function form(Schema $schema): Schema
    {
        return BidForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BidTable::configure($table);
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
            'index' => ListBids::route('/'),
            'create' => CreateBid::route('/create'),
            'edit' => EditBid::route('/{record}/edit'),
        ];
    }
}

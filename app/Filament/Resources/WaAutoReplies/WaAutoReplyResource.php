<?php

namespace App\Filament\Resources\WaAutoReplies;

use App\Filament\Resources\WaAutoReplies\Pages\CreateWaAutoReply;
use App\Filament\Resources\WaAutoReplies\Pages\EditWaAutoReply;
use App\Filament\Resources\WaAutoReplies\Pages\ListWaAutoReplies;
use App\Filament\Resources\WaAutoReplies\Schemas\WaAutoReplyForm;
use App\Filament\Resources\WaAutoReplies\Tables\WaAutoRepliesTable;
use App\Models\WaAutoReply;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class WaAutoReplyResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = WaAutoReply::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? Sales & CRM';
    }

    protected static ?string $label = 'Auto Reply WA';

    protected static ?string $pluralLabel = 'Auto Reply WA';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static ?int $navigationSort = 408;

    protected static ?string $recordTitleAttribute = 'keyword';

    public static function form(Schema $schema): Schema
    {
        return WaAutoReplyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaAutoRepliesTable::configure($table);
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
            'index' => ListWaAutoReplies::route('/'),
            'create' => CreateWaAutoReply::route('/create'),
            'edit' => EditWaAutoReply::route('/{record}/edit'),
        ];
    }
}
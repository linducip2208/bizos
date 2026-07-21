<?php

namespace App\Filament\Resources\WaConversations;

use App\Filament\Resources\WaConversations\Pages\CreateWaConversation;
use App\Filament\Resources\WaConversations\Pages\EditWaConversation;
use App\Filament\Resources\WaConversations\Pages\ListWaConversations;
use App\Filament\Resources\WaConversations\Schemas\WaConversationForm;
use App\Filament\Resources\WaConversations\Tables\WaConversationsTable;
use App\Models\WaConversation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class WaConversationResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = WaConversation::class;

    public static function getNavigationGroup(): string|null
    {
        return 'CRM';
    }

    protected static ?string $label = 'Percakapan WA';

    protected static ?string $pluralLabel = 'Percakapan WA';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 413;

    protected static ?string $recordTitleAttribute = 'contact_name';

    public static function form(Schema $schema): Schema
    {
        return WaConversationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaConversationsTable::configure($table);
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
            'index' => ListWaConversations::route('/'),
            'create' => CreateWaConversation::route('/create'),
            'edit' => EditWaConversation::route('/{record}/edit'),
        ];
    }
}

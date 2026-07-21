<?php

namespace App\Filament\Resources\AiConversations;

use App\Filament\Resources\AiConversations\Pages\CreateAiConversation;
use App\Filament\Resources\AiConversations\Pages\EditAiConversation;
use App\Filament\Resources\AiConversations\Pages\ListAiConversations;
use App\Filament\Resources\AiConversations\Schemas\AiConversationForm;
use App\Filament\Resources\AiConversations\Tables\AiConversationsTable;
use App\Models\AiConversation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class AiConversationResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = AiConversation::class;

    public static function getNavigationGroup(): string|null
    {
        return 'AI Assistant';
    }

    protected static ?string $label = 'Percakapan AI';

    protected static ?string $pluralLabel = 'Percakapan AI';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?int $navigationSort = 902;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return AiConversationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AiConversationsTable::configure($table);
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
            'index' => ListAiConversations::route('/'),
            'create' => CreateAiConversation::route('/create'),
            'edit' => EditAiConversation::route('/{record}/edit'),
        ];
    }
}
<?php

namespace App\Filament\Resources\ChatbotFlows;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ChatbotFlows\Pages\CreateChatbotFlow;
use App\Filament\Resources\ChatbotFlows\Pages\EditChatbotFlow;
use App\Filament\Resources\ChatbotFlows\Pages\ListChatbotFlows;
use App\Filament\Resources\ChatbotFlows\Schemas\ChatbotFlowForm;
use App\Filament\Resources\ChatbotFlows\Tables\ChatbotFlowsTable;
use App\Models\ChatbotFlow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChatbotFlowResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ChatbotFlow::class;

    public static function getNavigationGroup(): string|null
    {
        return 'CRM';
    }

    protected static ?string $label = 'Chatbot Flow';

    protected static ?string $pluralLabel = 'Chatbot Flow';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?int $navigationSort = 415;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ChatbotFlowForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatbotFlowsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatbotFlows::route('/'),
            'create' => CreateChatbotFlow::route('/create'),
            'edit' => EditChatbotFlow::route('/{record}/edit'),
        ];
    }
}

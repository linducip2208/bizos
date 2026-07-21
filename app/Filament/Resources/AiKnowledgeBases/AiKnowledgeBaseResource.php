<?php

namespace App\Filament\Resources\AiKnowledgeBases;

use App\Filament\Resources\AiKnowledgeBases\Pages\CreateAiKnowledgeBase;
use App\Filament\Resources\AiKnowledgeBases\Pages\EditAiKnowledgeBase;
use App\Filament\Resources\AiKnowledgeBases\Pages\ListAiKnowledgeBases;
use App\Filament\Resources\AiKnowledgeBases\Schemas\AiKnowledgeBaseForm;
use App\Filament\Resources\AiKnowledgeBases\Tables\AiKnowledgeBasesTable;
use App\Models\AiKnowledgeBase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class AiKnowledgeBaseResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = AiKnowledgeBase::class;

    public static function getNavigationGroup(): string|null
    {
        return 'AI Assistant';
    }

    protected static ?string $label = 'Knowledge Base';

    protected static ?string $pluralLabel = 'Knowledge Base';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 903;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return AiKnowledgeBaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AiKnowledgeBasesTable::configure($table);
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
            'index' => ListAiKnowledgeBases::route('/'),
            'create' => CreateAiKnowledgeBase::route('/create'),
            'edit' => EditAiKnowledgeBase::route('/{record}/edit'),
        ];
    }
}

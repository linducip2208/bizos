<?php

namespace App\Filament\Resources\TaskComments;

use App\Filament\Resources\TaskComments\Pages\CreateTaskComment;
use App\Filament\Resources\TaskComments\Pages\EditTaskComment;
use App\Filament\Resources\TaskComments\Pages\ListTaskComments;
use App\Filament\Resources\TaskComments\Schemas\TaskCommentForm;
use App\Filament\Resources\TaskComments\Tables\TaskCommentsTable;
use App\Models\TaskComment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class TaskCommentResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
{
    use HasPermissionAccess;
    protected static ?string $model = TaskComment::class;

    public static function getNavigationGroup(): string|null
    {
        return '📋 Project Management';
    }

    protected static ?string $label = 'Komentar Tugas';

    protected static ?string $pluralLabel = 'Komentar Tugas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleOvalLeft;

    protected static ?int $navigationSort = 508;

    protected static ?string $recordTitleAttribute = 'comment';

    public static function form(Schema $schema): Schema
    {
        return TaskCommentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaskCommentsTable::configure($table);
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
            'index' => ListTaskComments::route('/'),
            'create' => CreateTaskComment::route('/create'),
            'edit' => EditTaskComment::route('/{record}/edit'),
        ];
    }
}
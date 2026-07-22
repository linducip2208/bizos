<?php

namespace App\Filament\Resources\TaskAttachments;

use App\Filament\Resources\TaskAttachments\Pages\CreateTaskAttachment;
use App\Filament\Resources\TaskAttachments\Pages\EditTaskAttachment;
use App\Filament\Resources\TaskAttachments\Pages\ListTaskAttachments;
use App\Filament\Resources\TaskAttachments\Schemas\TaskAttachmentForm;
use App\Filament\Resources\TaskAttachments\Tables\TaskAttachmentsTable;
use App\Models\TaskAttachment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class TaskAttachmentResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;
    protected static ?string $model = TaskAttachment::class;

    public static function getNavigationGroup(): string|null
    {
        return '📋 Project Management';
    }

    protected static ?string $label = 'Lampiran Tugas';

    protected static ?string $pluralLabel = 'Lampiran Tugas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperClip;

    protected static ?int $navigationSort = 509;

    protected static ?string $recordTitleAttribute = 'file_name';

    public static function form(Schema $schema): Schema
    {
        return TaskAttachmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaskAttachmentsTable::configure($table);
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
            'index' => ListTaskAttachments::route('/'),
            'create' => CreateTaskAttachment::route('/create'),
            'edit' => EditTaskAttachment::route('/{record}/edit'),
        ];
    }
}
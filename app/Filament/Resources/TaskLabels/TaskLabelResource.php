<?php

namespace App\Filament\Resources\TaskLabels;

use App\Filament\Resources\TaskLabels\Pages\CreateTaskLabel;
use App\Filament\Resources\TaskLabels\Pages\EditTaskLabel;
use App\Filament\Resources\TaskLabels\Pages\ListTaskLabels;
use App\Filament\Resources\TaskLabels\Schemas\TaskLabelForm;
use App\Filament\Resources\TaskLabels\Tables\TaskLabelTable;
use App\Models\TaskLabel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class TaskLabelResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = TaskLabel::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Project';
    }

    protected static ?string $label = 'Label Tugas';

    protected static ?string $pluralLabel = 'Label Tugas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?int $navigationSort = 504;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TaskLabelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaskLabelTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaskLabels::route('/'),
            'create' => CreateTaskLabel::route('/create'),
            'edit' => EditTaskLabel::route('/{record}/edit'),
        ];
    }
}

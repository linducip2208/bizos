<?php

namespace App\Filament\Resources\PipelineStages;

use App\Filament\Resources\PipelineStages\Pages\CreatePipelineStage;
use App\Filament\Resources\PipelineStages\Pages\EditPipelineStage;
use App\Filament\Resources\PipelineStages\Pages\ListPipelineStages;
use App\Filament\Resources\PipelineStages\Schemas\PipelineStageForm;
use App\Filament\Resources\PipelineStages\Tables\PipelineStagesTable;
use App\Models\PipelineStage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PipelineStageResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PipelineStage::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? Sales & CRM';
    }

    protected static ?string $label = 'Tahap Pipeline';

    protected static ?string $pluralLabel = 'Tahap Pipeline';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 404;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PipelineStageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PipelineStagesTable::configure($table);
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
            'index' => ListPipelineStages::route('/'),
            'create' => CreatePipelineStage::route('/create'),
            'edit' => EditPipelineStage::route('/{record}/edit'),
        ];
    }
}
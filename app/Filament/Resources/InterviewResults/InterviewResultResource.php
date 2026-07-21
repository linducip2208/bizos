<?php

namespace App\Filament\Resources\InterviewResults;

use App\Filament\Resources\InterviewResults\Pages\CreateInterviewResult;
use App\Filament\Resources\InterviewResults\Pages\EditInterviewResult;
use App\Filament\Resources\InterviewResults\Pages\ListInterviewResults;
use App\Filament\Resources\InterviewResults\Schemas\InterviewResultForm;
use App\Filament\Resources\InterviewResults\Tables\InterviewResultsTable;
use App\Models\InterviewResult;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class InterviewResultResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = InterviewResult::class;

    public static function getNavigationGroup(): string|null
    {
        return 'HRM';
    }

    protected static ?string $label = 'Hasil Interview';

    protected static ?string $pluralLabel = 'Hasil Interview';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?int $navigationSort = 116;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return InterviewResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InterviewResultsTable::configure($table);
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
            'index' => ListInterviewResults::route('/'),
            'create' => CreateInterviewResult::route('/create'),
            'edit' => EditInterviewResult::route('/{record}/edit'),
        ];
    }
}

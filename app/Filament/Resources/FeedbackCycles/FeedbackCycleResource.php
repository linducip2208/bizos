<?php

namespace App\Filament\Resources\FeedbackCycles;

use App\Filament\Resources\FeedbackCycles\Pages\CreateFeedbackCycle;
use App\Filament\Resources\FeedbackCycles\Pages\EditFeedbackCycle;
use App\Filament\Resources\FeedbackCycles\Pages\ListFeedbackCycles;
use App\Filament\Resources\FeedbackCycles\Schemas\FeedbackCycleForm;
use App\Filament\Resources\FeedbackCycles\Tables\FeedbackCyclesTable;
use App\Models\FeedbackCycle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class FeedbackCycleResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = FeedbackCycle::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::HUMAN_CAPITAL->value;
    }

    protected static ?string $label = 'Feedback Cycles';

    protected static ?string $pluralLabel = 'Feedback Cycles';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static ?int $navigationSort = 105;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FeedbackCycleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedbackCyclesTable::configure($table);
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
            'index' => ListFeedbackCycles::route('/'),
            'create' => CreateFeedbackCycle::route('/create'),
            'edit' => EditFeedbackCycle::route('/{record}/edit'),
        ];
    }
}
<?php

namespace App\Filament\Resources\FeedbackReviewers;

use App\Filament\Resources\FeedbackReviewers\Pages\CreateFeedbackReviewer;
use App\Filament\Resources\FeedbackReviewers\Pages\EditFeedbackReviewer;
use App\Filament\Resources\FeedbackReviewers\Pages\ListFeedbackReviewers;
use App\Filament\Resources\FeedbackReviewers\Schemas\FeedbackReviewerForm;
use App\Filament\Resources\FeedbackReviewers\Tables\FeedbackReviewersTable;
use App\Models\FeedbackReviewer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class FeedbackReviewerResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;
    protected static ?string $model = FeedbackReviewer::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::HUMAN_CAPITAL->value;
    }

    protected static ?string $label = 'Feedback Reviewers';

    protected static ?string $pluralLabel = 'Feedback Reviewers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?int $navigationSort = 118;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return FeedbackReviewerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedbackReviewersTable::configure($table);
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
            'index' => ListFeedbackReviewers::route('/'),
            'create' => CreateFeedbackReviewer::route('/create'),
            'edit' => EditFeedbackReviewer::route('/{record}/edit'),
        ];
    }
}
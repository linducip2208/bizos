<?php

namespace App\Filament\Resources\FeedbackQuestions;

use App\Filament\Resources\FeedbackQuestions\Pages\CreateFeedbackQuestion;
use App\Filament\Resources\FeedbackQuestions\Pages\EditFeedbackQuestion;
use App\Filament\Resources\FeedbackQuestions\Pages\ListFeedbackQuestions;
use App\Filament\Resources\FeedbackQuestions\Schemas\FeedbackQuestionForm;
use App\Filament\Resources\FeedbackQuestions\Tables\FeedbackQuestionsTable;
use App\Models\FeedbackQuestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class FeedbackQuestionResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = FeedbackQuestion::class;

    public static function getNavigationGroup(): string|null
    {
        return '👥 Human Capital';
    }

    protected static ?string $label = 'Pertanyaan Feedback';

    protected static ?string $pluralLabel = 'Pertanyaan Feedback';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?int $navigationSort = 117;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return FeedbackQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedbackQuestionsTable::configure($table);
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
            'index' => ListFeedbackQuestions::route('/'),
            'create' => CreateFeedbackQuestion::route('/create'),
            'edit' => EditFeedbackQuestion::route('/{record}/edit'),
        ];
    }
}
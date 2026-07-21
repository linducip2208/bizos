<?php

namespace App\Filament\Resources\FeedbackAnswers;

use App\Filament\Resources\FeedbackAnswers\Pages\CreateFeedbackAnswer;
use App\Filament\Resources\FeedbackAnswers\Pages\EditFeedbackAnswer;
use App\Filament\Resources\FeedbackAnswers\Pages\ListFeedbackAnswers;
use App\Filament\Resources\FeedbackAnswers\Schemas\FeedbackAnswerForm;
use App\Filament\Resources\FeedbackAnswers\Tables\FeedbackAnswersTable;
use App\Models\FeedbackAnswer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class FeedbackAnswerResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = FeedbackAnswer::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? HR & Payroll';
    }

    protected static ?string $label = 'Jawaban Feedback';

    protected static ?string $pluralLabel = 'Jawaban Feedback';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?int $navigationSort = 119;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return FeedbackAnswerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedbackAnswersTable::configure($table);
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
            'index' => ListFeedbackAnswers::route('/'),
            'create' => CreateFeedbackAnswer::route('/create'),
            'edit' => EditFeedbackAnswer::route('/{record}/edit'),
        ];
    }
}
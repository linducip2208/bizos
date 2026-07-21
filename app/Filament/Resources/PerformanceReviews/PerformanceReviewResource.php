<?php

namespace App\Filament\Resources\PerformanceReviews;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\PerformanceReviews\Pages\CreatePerformanceReview;
use App\Filament\Resources\PerformanceReviews\Pages\EditPerformanceReview;
use App\Filament\Resources\PerformanceReviews\Pages\ListPerformanceReviews;
use App\Filament\Resources\PerformanceReviews\Schemas\PerformanceReviewForm;
use App\Filament\Resources\PerformanceReviews\Tables\PerformanceReviewsTable;
use App\Models\PerformanceReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PerformanceReviewResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PerformanceReview::class;

    public static function getNavigationGroup(): string|null
    {
        return 'HRM';
    }

    protected static ?string $label = 'Review Performa';
    protected static ?string $pluralLabel = 'Review Performa';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;
    protected static ?int $navigationSort = 129;
    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PerformanceReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PerformanceReviewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPerformanceReviews::route('/'),
            'create' => CreatePerformanceReview::route('/create'),
            'edit' => EditPerformanceReview::route('/{record}/edit'),
        ];
    }
}

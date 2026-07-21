<?php

namespace App\Filament\Resources\Interviews;

use App\Filament\Resources\Interviews\Pages\CreateInterview;
use App\Filament\Resources\Interviews\Pages\EditInterview;
use App\Filament\Resources\Interviews\Pages\ListInterviews;
use App\Filament\Resources\Interviews\Schemas\InterviewForm;
use App\Filament\Resources\Interviews\Tables\InterviewsTable;
use App\Models\Interview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class InterviewResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = Interview::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? HR & Payroll';
    }

    protected static ?string $label = 'Interview';

    protected static ?string $pluralLabel = 'Interview';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 113;

    public static function form(Schema $schema): Schema
    {
        return InterviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InterviewsTable::configure($table);
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
            'index' => ListInterviews::route('/'),
            'create' => CreateInterview::route('/create'),
            'edit' => EditInterview::route('/{record}/edit'),
        ];
    }
}
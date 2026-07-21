<?php

namespace App\Filament\Resources\DpiaAssessments;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\DpiaAssessments\Pages\CreateDpiaAssessment;
use App\Filament\Resources\DpiaAssessments\Pages\EditDpiaAssessment;
use App\Filament\Resources\DpiaAssessments\Pages\ListDpiaAssessments;
use App\Filament\Resources\DpiaAssessments\Schemas\DpiaAssessmentForm;
use App\Filament\Resources\DpiaAssessments\Tables\DpiaAssessmentTable;
use App\Models\DpiaAssessment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DpiaAssessmentResource extends Resource
{
    protected static ?string $model = DpiaAssessment::class;

    protected static ?string $label = 'DPIA';

    protected static ?string $pluralLabel = 'DPIA';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string|null
    {
        return 'Kepatuhan';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDpiaAssessments::route('/'),
            'create' => CreateDpiaAssessment::route('/create'),
            'edit' => EditDpiaAssessment::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return DpiaAssessmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DpiaAssessmentTable::configure($table);
    }
}

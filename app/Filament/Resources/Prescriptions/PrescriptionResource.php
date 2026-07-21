<?php

namespace App\Filament\Resources\Prescriptions;

use App\Filament\Resources\Prescriptions\Pages\CreatePrescription;
use App\Filament\Resources\Prescriptions\Pages\EditPrescription;
use App\Filament\Resources\Prescriptions\Pages\ListPrescriptions;
use App\Filament\Resources\Prescriptions\Schemas\PrescriptionForm;
use App\Filament\Resources\Prescriptions\Tables\PrescriptionTable;
use App\Models\Prescription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Concerns\HasPermissionAccess;

class PrescriptionResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Prescription::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? Industri';
    }

    protected static ?string $label = 'Resep';

    protected static ?string $pluralLabel = 'Resep';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 1004;

    protected static ?string $recordTitleAttribute = 'prescription_date';

    public static function form(Schema $schema): Schema
    {
        return PrescriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrescriptionTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrescriptions::route('/'),
            'create' => CreatePrescription::route('/create'),
            'edit' => EditPrescription::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
<?php

namespace App\Filament\Resources\FormFields;

use App\Filament\Resources\FormFields\Pages\CreateFormField;
use App\Filament\Resources\FormFields\Pages\EditFormField;
use App\Filament\Resources\FormFields\Pages\ListFormFields;
use App\Filament\Resources\FormFields\Schemas\FormFieldForm;
use App\Filament\Resources\FormFields\Tables\FormFieldsTable;
use App\Models\FormField;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class FormFieldResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;
    protected static ?string $model = FormField::class;

    public static function getNavigationGroup(): string|null
    {
        return '💬 Collaboration';
    }

    protected static ?string $label = 'Field Formulir';

    protected static ?string $pluralLabel = 'Field Formulir';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 708;

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return FormFieldForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormFieldsTable::configure($table);
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
            'index' => ListFormFields::route('/'),
            'create' => CreateFormField::route('/create'),
            'edit' => EditFormField::route('/{record}/edit'),
        ];
    }
}
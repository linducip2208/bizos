<?php

namespace App\Filament\Resources\BizForms;

use App\Filament\Resources\BizForms\Pages\CreateForm;
use App\Filament\Resources\BizForms\Pages\EditForm;
use App\Filament\Resources\BizForms\Pages\ListForms;
use App\Filament\Resources\BizForms\Schemas\BizFormForm;
use App\Filament\Resources\BizForms\Tables\BizFormsTable;
use App\Models\Form as FormModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class FormResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = FormModel::class;

    protected static ?string $label = 'Formulir';

    protected static ?string $pluralLabel = 'Formulir';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 703;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'forms';
    }

    public static function getNavigationGroup(): string|null
    {
        return '💬 Collaboration';
    }

    public static function form(Schema $schema): Schema
    {
        return BizFormForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BizFormsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForms::route('/'),
            'create' => CreateForm::route('/create'),
            'edit' => EditForm::route('/{record}/edit'),
        ];
    }
}
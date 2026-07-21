<?php

namespace App\Filament\Resources\WaTemplates;

use App\Filament\Resources\WaTemplates\Pages\CreateWaTemplate;
use App\Filament\Resources\WaTemplates\Pages\EditWaTemplate;
use App\Filament\Resources\WaTemplates\Pages\ListWaTemplates;
use App\Filament\Resources\WaTemplates\Schemas\WaTemplateForm;
use App\Filament\Resources\WaTemplates\Tables\WaTemplatesTable;
use App\Models\WaTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class WaTemplateResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = WaTemplate::class;

    public static function getNavigationGroup(): string|null
    {
        return 'CRM';
    }

    protected static ?string $label = 'Template WA';

    protected static ?string $pluralLabel = 'Template WA';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?int $navigationSort = 406;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return WaTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaTemplatesTable::configure($table);
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
            'index' => ListWaTemplates::route('/'),
            'create' => CreateWaTemplate::route('/create'),
            'edit' => EditWaTemplate::route('/{record}/edit'),
        ];
    }
}
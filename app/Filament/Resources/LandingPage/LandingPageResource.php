<?php

namespace App\Filament\Resources\LandingPage;

use App\Filament\Resources\LandingPage\Pages\CreateLandingPage;
use App\Filament\Resources\LandingPage\Pages\EditLandingPage;
use App\Filament\Resources\LandingPage\Pages\ListLandingPages;
use App\Filament\Resources\LandingPage\Schemas\LandingPageForm;
use App\Filament\Resources\LandingPage\Tables\LandingPagesTable;
use App\Models\LandingPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class LandingPageResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = LandingPage::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Marketing';
    }

    protected static ?string $label = 'Landing Page';

    protected static ?string $pluralLabel = 'Landing Page';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 1302;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return LandingPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LandingPagesTable::configure($table);
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
            'index' => ListLandingPages::route('/'),
            'create' => CreateLandingPage::route('/create'),
            'edit' => EditLandingPage::route('/{record}/edit'),
        ];
    }
}

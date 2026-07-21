<?php

namespace App\Filament\Resources\WikiPage;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\WikiPage\Pages\ListWikiPages;
use App\Filament\Resources\WikiPage\Pages\CreateWikiPage;
use App\Filament\Resources\WikiPage\Pages\EditWikiPage;
use App\Filament\Resources\WikiPage\Schemas\WikiPageForm;
use App\Filament\Resources\WikiPage\Tables\WikiPageTable;
use App\Models\WikiPage;

class WikiPageResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = WikiPage::class;
    public static function getNavigationGroup(): string|null { return '💬 Collaboration'; }
    protected static ?string $label = 'Halaman Wiki';
    protected static ?string $pluralLabel = 'Halaman Wiki';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?int $navigationSort = 711;
    protected static ?string $recordTitleAttribute = 'title';
    public static function form(Schema $schema): Schema { return WikiPageForm::configure($schema); }
    public static function table(Table $table): Table { return WikiPageTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return [
        'index' => ListWikiPages::route('/'),
        'create' => CreateWikiPage::route('/create'),
        'edit' => EditWikiPage::route('/{record}/edit'),
    ];}
}
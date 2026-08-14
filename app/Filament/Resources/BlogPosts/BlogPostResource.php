<?php

namespace App\Filament\Resources\BlogPosts;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use App\Filament\Resources\BlogPosts\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Filament\Resources\BlogPosts\Schemas\BlogPostForm;
use App\Filament\Resources\BlogPosts\Tables\BlogPostTable;
use App\Models\BlogPost;

class BlogPostResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = BlogPost::class;
    public static function getNavigationGroup(): string|null { return '📢 Marketing'; }
    protected static ?string $label = 'Post Blog';
    protected static ?string $pluralLabel = 'Post Blog';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?int $navigationSort = 1401;
    protected static ?string $recordTitleAttribute = 'title';
    public static function form(Schema $schema): Schema { return BlogPostForm::configure($schema); }
    public static function table(Table $table): Table { return BlogPostTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return [
        'index' => ListBlogPosts::route('/'),
        'create' => CreateBlogPost::route('/create'),
        'edit' => EditBlogPost::route('/{record}/edit'),
    ];}
}

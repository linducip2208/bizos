<?php

namespace App\Filament\Resources\NoticeBoards;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\NoticeBoards\Pages\ListNoticeBoards;
use App\Filament\Resources\NoticeBoards\Pages\CreateNoticeBoard;
use App\Filament\Resources\NoticeBoards\Pages\EditNoticeBoard;
use App\Filament\Resources\NoticeBoards\Schemas\NoticeBoardForm;
use App\Filament\Resources\NoticeBoards\Tables\NoticeBoardTable;
use App\Models\NoticeBoardPost;

class NoticeBoardResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = NoticeBoardPost::class;
    public static function getNavigationGroup(): string|null { return '?? Project & Work'; }
    protected static ?string $label = 'Papan Pengumuman';
    protected static ?string $pluralLabel = 'Papan Pengumuman';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;
    protected static ?int $navigationSort = 712;
    protected static ?string $recordTitleAttribute = 'title';
    public static function form(Schema $schema): Schema { return NoticeBoardForm::configure($schema); }
    public static function table(Table $table): Table { return NoticeBoardTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return [
        'index' => ListNoticeBoards::route('/'),
        'create' => CreateNoticeBoard::route('/create'),
        'edit' => EditNoticeBoard::route('/{record}/edit'),
    ];}
}
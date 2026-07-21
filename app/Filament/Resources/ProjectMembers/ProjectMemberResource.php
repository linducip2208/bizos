<?php

namespace App\Filament\Resources\ProjectMembers;

use App\Filament\Resources\ProjectMembers\Pages\CreateProjectMember;
use App\Filament\Resources\ProjectMembers\Pages\EditProjectMember;
use App\Filament\Resources\ProjectMembers\Pages\ListProjectMembers;
use App\Filament\Resources\ProjectMembers\Schemas\ProjectMemberForm;
use App\Filament\Resources\ProjectMembers\Tables\ProjectMembersTable;
use App\Models\ProjectMember;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class ProjectMemberResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = ProjectMember::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Project';
    }

    protected static ?string $label = 'Anggota Project';

    protected static ?string $pluralLabel = 'Anggota Project';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 507;

    protected static ?string $recordTitleAttribute = 'role';

    public static function form(Schema $schema): Schema
    {
        return ProjectMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectMembersTable::configure($table);
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
            'index' => ListProjectMembers::route('/'),
            'create' => CreateProjectMember::route('/create'),
            'edit' => EditProjectMember::route('/{record}/edit'),
        ];
    }
}
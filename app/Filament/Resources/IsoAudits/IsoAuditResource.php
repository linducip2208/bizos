<?php

namespace App\Filament\Resources\IsoAudits;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\IsoAudits\Pages\CreateIsoAudit;
use App\Filament\Resources\IsoAudits\Pages\EditIsoAudit;
use App\Filament\Resources\IsoAudits\Pages\ListIsoAudits;
use App\Filament\Resources\IsoAudits\Schemas\IsoAuditForm;
use App\Filament\Resources\IsoAudits\Tables\IsoAuditTable;
use App\Models\IsoAudit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IsoAuditResource extends Resource
{
    protected static ?string $model = IsoAudit::class;

    protected static ?string $label = 'Audit ISO';

    protected static ?string $pluralLabel = 'Audit ISO';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): string|null
    {
        return 'Kepatuhan';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIsoAudits::route('/'),
            'create' => CreateIsoAudit::route('/create'),
            'edit' => EditIsoAudit::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return IsoAuditForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IsoAuditTable::configure($table);
    }
}
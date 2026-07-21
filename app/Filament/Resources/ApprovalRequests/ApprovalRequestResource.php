<?php

namespace App\Filament\Resources\ApprovalRequests;

use App\Filament\Resources\ApprovalRequests\Pages\EditApprovalRequest;
use App\Filament\Resources\ApprovalRequests\Pages\ListApprovalRequests;
use App\Filament\Resources\ApprovalRequests\Pages\ViewApprovalRequest;
use App\Filament\Resources\ApprovalRequests\Tables\ApprovalRequestsTable;
use App\Models\ApprovalRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class ApprovalRequestResource extends Resource
{
    protected static ?string $model = ApprovalRequest::class;

    protected static ?string $label = 'Permintaan Approval';

    protected static ?string $pluralLabel = 'Permintaan Approval';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 1051;

    public static function getNavigationGroup(): string|null
    {
        return 'Sistem';
    }

    public static function table(Table $table): Table
    {
        return ApprovalRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApprovalRequests::route('/'),
            'edit' => EditApprovalRequest::route('/{record}/edit'),
            'view' => ViewApprovalRequest::route('/{record}'),
        ];
    }
}

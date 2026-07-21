<?php

namespace App\Filament\Resources\ApprovalWorkflows;

use App\Filament\Resources\ApprovalWorkflows\Pages\CreateApprovalWorkflow;
use App\Filament\Resources\ApprovalWorkflows\Pages\EditApprovalWorkflow;
use App\Filament\Resources\ApprovalWorkflows\Pages\ListApprovalWorkflows;
use App\Filament\Resources\ApprovalWorkflows\Schemas\ApprovalWorkflowForm;
use App\Filament\Resources\ApprovalWorkflows\Tables\ApprovalWorkflowsTable;
use App\Models\ApprovalWorkflow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ApprovalWorkflowResource extends Resource
{
    protected static ?string $model = ApprovalWorkflow::class;

    protected static ?string $label = 'Approval Workflow';

    protected static ?string $pluralLabel = 'Approval Workflow';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static ?int $navigationSort = 1050;

    public static function getNavigationGroup(): string|null
    {
        return 'Sistem';
    }

    public static function form(Schema $schema): Schema
    {
        return ApprovalWorkflowForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApprovalWorkflowsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApprovalWorkflows::route('/'),
            'create' => CreateApprovalWorkflow::route('/create'),
            'edit' => EditApprovalWorkflow::route('/{record}/edit'),
        ];
    }
}

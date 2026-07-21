<?php

namespace App\Filament\Resources\ApprovalWorkflows\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApprovalWorkflowsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('module')
                    ->label('Modul')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'leave' => 'Cuti',
                        'reimbursement' => 'Reimbursement',
                        'budget' => 'Budget',
                        'purchase_requisition' => 'Purchase Requisition',
                        'purchase_order' => 'Purchase Order',
                        'overtime' => 'Lembur',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'leave' => 'info',
                        'reimbursement' => 'warning',
                        'budget' => 'success',
                        'purchase_requisition' => 'danger',
                        'purchase_order' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('levels_count')
                    ->label('Level')
                    ->counts('levels')
                    ->alignCenter(),
                TextColumn::make('min_approvers')
                    ->label('Min. Approver')
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
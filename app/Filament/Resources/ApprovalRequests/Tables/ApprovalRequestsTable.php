<?php

namespace App\Filament\Resources\ApprovalRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApprovalRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

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

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                TextColumn::make('requester.first_name')
                    ->label('Pengaju')
                    ->formatStateUsing(fn ($record) => $record->requester
                        ? $record->requester->first_name . ' ' . $record->requester->last_name
                        : '-'),

                TextColumn::make('current_level')
                    ->label('Level')
                    ->formatStateUsing(fn ($record) => "{$record->current_level}/{$record->total_levels}")
                    ->alignCenter(),

                TextColumn::make('workflow.name')
                    ->label('Workflow')
                    ->searchable(),

                TextColumn::make('submitted_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                    ]),
                SelectFilter::make('module')
                    ->label('Modul')
                    ->options([
                        'leave' => 'Cuti',
                        'overtime' => 'Lembur',
                        'reimbursement' => 'Reimbursement',
                        'budget' => 'Budget',
                        'purchase_requisition' => 'Purchase Requisition',
                        'purchase_order' => 'Purchase Order',
                    ]),
                SelectFilter::make('workflow_id')
                    ->label('Workflow')
                    ->relationship('workflow', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
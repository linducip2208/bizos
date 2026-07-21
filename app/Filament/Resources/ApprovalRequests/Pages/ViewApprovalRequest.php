<?php

namespace App\Filament\Resources\ApprovalRequests\Pages;

use App\Filament\Resources\ApprovalRequests\ApprovalRequestResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class ViewApprovalRequest extends ViewRecord
{
    protected static string $resource = ApprovalRequestResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Permintaan')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')
                            ->label('Judul'),
                        TextEntry::make('module')
                            ->label('Modul')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'leave' => 'Cuti',
                                'reimbursement' => 'Reimbursement',
                                'budget' => 'Budget',
                                'purchase_requisition' => 'Purchase Requisition',
                                'purchase_order' => 'Purchase Order',
                                default => ucfirst($state),
                            }),
                        TextEntry::make('status')
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
                        TextEntry::make('requester.first_name')
                            ->label('Pengaju')
                            ->formatStateUsing(fn ($record) => $record->requester
                                ? $record->requester->first_name . ' ' . $record->requester->last_name
                                : '-'),
                        TextEntry::make('current_level')
                            ->label('Level Saat Ini')
                            ->formatStateUsing(fn ($record) => "{$record->current_level} / {$record->total_levels}"),
                        TextEntry::make('workflow.name')
                            ->label('Workflow'),
                        TextEntry::make('submitted_at')
                            ->label('Tanggal Submit')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('completed_at')
                            ->label('Tanggal Selesai')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),

                Section::make('Riwayat Approval')
                    ->schema([
                        RepeatableEntry::make('actions')
                            ->label('')
                            ->hiddenLabel()
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('action')
                                            ->label('Aksi')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'approve' => 'success',
                                                'reject' => 'danger',
                                                'delegate' => 'warning',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                'approve' => 'Disetujui',
                                                'reject' => 'Ditolak',
                                                'delegate' => 'Didelegasikan',
                                                default => $state,
                                            }),
                                        TextEntry::make('approver.first_name')
                                            ->label('Oleh')
                                            ->formatStateUsing(fn ($record) => $record->approver
                                                ? $record->approver->first_name . ' ' . $record->approver->last_name
                                                : '-'),
                                        TextEntry::make('action_at')
                                            ->label('Tanggal')
                                            ->dateTime('d M Y H:i'),
                                        TextEntry::make('comment')
                                            ->label('Komentar')
                                            ->placeholder('-'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}

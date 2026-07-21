<?php

namespace App\Filament\Resources\ActivityTimeline\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityTimelineTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'Dibuat',
                        'updated' => 'Diperbarui',
                        'deleted' => 'Dihapus',
                        'status_changed' => 'Status Berubah',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'primary',
                        'deleted' => 'danger',
                        'status_changed' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('model_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'employee' => 'Karyawan',
                        'department' => 'Departemen',
                        'leave' => 'Cuti',
                        'reimbursement' => 'Reimbursement',
                        'overtime' => 'Lembur',
                        'purchase_requisition' => 'PR',
                        'purchase_order' => 'PO',
                        'invoice' => 'Invoice',
                        'ticket' => 'Tiket',
                        'task' => 'Tugas',
                        'project' => 'Proyek',
                        'budget' => 'Anggaran',
                        'asset' => 'Aset',
                        'product' => 'Produk',
                        default => class_basename($state),
                    }),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([]);
    }
}

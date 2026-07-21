<?php

namespace App\Filament\Resources\GamificationBadge\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BadgeTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Badge')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                TextColumn::make('trigger_action')
                    ->label('Trigger Aksi')
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'clock_in_on_time' => 'Clock-in Tepat Waktu',
                        'clock_in_early' => 'Clock-in Lebih Awal',
                        'task_completed_before_deadline' => 'Tugas Sebelum Deadline',
                        'task_completed_on_time' => 'Tugas Tepat Waktu',
                        'ticket_resolved_under_sla' => 'Tiket Di Bawah SLA',
                        'ticket_resolved' => 'Tiket Selesai',
                        'deal_won' => 'Deal Won',
                        'lead_converted' => 'Lead Terkonversi',
                        'course_completed' => 'Kursus Selesai',
                        'quiz_passed' => 'Quiz Lulus',
                        'attendance_perfect_week' => 'Kehadiran Mingguan',
                        'attendance_perfect_month' => 'Kehadiran Bulanan',
                        'overtime_volunteer' => 'Relawan Lembur',
                        'peer_recognition' => 'Pengakuan Rekan',
                        default => $state ?? '-',
                    })
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('trigger_count')
                    ->label('Trigger')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('points_reward')
                    ->label('Bonus Poin')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('name')
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
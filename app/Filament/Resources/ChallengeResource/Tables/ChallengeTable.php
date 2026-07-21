<?php

namespace App\Filament\Resources\ChallengeResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChallengeTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                TextColumn::make('target_action')
                    ->label('Target Aksi')
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
                    ->sortable(),
                TextColumn::make('target_count')
                    ->label('Target')
                    ->sortable(),
                TextColumn::make('points_reward')
                    ->label('Reward Poin')
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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

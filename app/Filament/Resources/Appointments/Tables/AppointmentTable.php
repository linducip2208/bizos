<?php

namespace App\Filament\Resources\Appointments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('appointment_date', 'desc')
            ->columns([
                TextColumn::make('appointment_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Jam')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('H:i'))
                    ->sortable(),
                TextColumn::make('queue_number')
                    ->label('Antrian')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('patient.full_name')
                    ->label('Pasien')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('doctor.first_name')
                    ->label('Dokter')
                    ->formatStateUsing(fn ($record) => $record->doctor?->first_name . ' ' . $record->doctor?->last_name)
                    ->searchable(),
                TextColumn::make('appointment_type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'consultation' => 'Konsultasi',
                        'treatment' => 'Tindakan',
                        'checkup' => 'Check-up',
                        'vaccination' => 'Vaksinasi',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'consultation' => 'info',
                        'treatment' => 'warning',
                        'checkup' => 'success',
                        'vaccination' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Terjadwal',
                        'confirmed' => 'Dikonfirmasi',
                        'arrived' => 'Sudah Datang',
                        'in_progress' => 'Diperiksa',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'no_show' => 'Tidak Hadir',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'confirmed' => 'primary',
                        'arrived' => 'warning',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'no_show' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                TrashedFilter::make(),
                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('appointment_date', now()->toDateString())),
                Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('appointment_date', [
                        now()->startOfWeek()->toDateString(),
                        now()->endOfWeek()->toDateString(),
                    ])),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
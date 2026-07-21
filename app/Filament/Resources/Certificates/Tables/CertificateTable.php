<?php

namespace App\Filament\Resources\Certificates\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class CertificateTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('certificate_number')
                    ->label('Nomor Sertifikat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('enrollment.course.title')
                    ->label('Kursus')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('enrollment.employee.first_name')
                    ->label('Peserta')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->enrollment?->employee
                        ? $record->enrollment->employee->first_name . ' ' . $record->enrollment->employee->last_name
                        : '-')
                    ->sortable(),
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->copyable()
                    ->limit(8)
                    ->tooltip(fn ($record) => $record->uuid),
                TextColumn::make('issued_date')
                    ->label('Tanggal Terbit')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('enrollment.completed_at')
                    ->label('Selesai Kursus')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->recordActions([
                Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => $record->pdf_path && Storage::exists($record->pdf_path)
                        ? Storage::url($record->pdf_path)
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->pdf_path && Storage::exists($record->pdf_path)),
                Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('warning')
                    ->action(function ($record) {
                        \Filament\Notifications\Notification::make()
                            ->title('Hasil Verifikasi')
                            ->body("Sertifikat #{$record->certificate_number} — UUID: {$record->uuid}")
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                SelectFilter::make('course')
                    ->label('Kursus')
                    ->relationship('enrollment.course', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('employee')
                    ->label('Peserta')
                    ->relationship('enrollment.employee', 'first_name')
                    ->searchable()
                    ->preload(),
                Filter::make('issued_date')
                    ->label('Tanggal Terbit')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('issued_from')
                            ->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('issued_until')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['issued_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('issued_date', '>=', $date),
                            )
                            ->when(
                                $data['issued_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('issued_date', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('issued_date', 'desc');
    }
}

<?php

namespace App\Filament\Resources\Prescriptions\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Services\HealthcareService;
use Filament\Notifications\Notification;

class PrescriptionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('prescription_date', 'desc')
            ->columns([
                TextColumn::make('prescription_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('patient.full_name')
                    ->label('Pasien')
                    ->searchable(),
                TextColumn::make('doctor.first_name')
                    ->label('Dokter')
                    ->formatStateUsing(fn ($record) => $record->doctor?->first_name . ' ' . $record->doctor?->last_name)
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label('Jumlah Obat')
                    ->counts('items')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'dispensed' => 'Diserahkan',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'dispensed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('dispense')
                    ->label('Serahkan Obat')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(function ($record) {
                        app(HealthcareService::class)->dispensePrescription($record);
                        Notification::make()
                            ->title('Obat berhasil diserahkan')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Serahkan Obat')
                    ->modalDescription('Stok akan otomatis berkurang. Pastikan stok mencukupi.'),
                Action::make('check_interaction')
                    ->label('Cek Interaksi Obat')
                    ->icon('heroicon-o-beaker')
                    ->color('warning')
                    ->action(function ($record) {
                        $drugIds = $record->items->pluck('product_id')->toArray();
                        $interactions = app(HealthcareService::class)->checkDrugInteraction($drugIds);

                        if (empty($interactions)) {
                            Notification::make()
                                ->title('Tidak ada interaksi obat terdeteksi')
                                ->success()
                                ->send();
                        } else {
                            $body = collect($interactions)->map(fn ($i) =>
                                "{$i['drug_a']} + {$i['drug_b']}\nSeverity: {$i['severity']}\n{$i['description']}\nRekomendasi: {$i['recommendation']}"
                            )->join("\n\n——\n\n");

                            Notification::make()
                                ->title('Interaksi Obat Terdeteksi!')
                                ->body($body)
                                ->warning()
                                ->persistent()
                                ->send();
                        }
                    }),
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

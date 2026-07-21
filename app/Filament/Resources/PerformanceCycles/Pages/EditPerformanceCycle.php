<?php

namespace App\Filament\Resources\PerformanceCycles\Pages;

use App\Filament\Resources\PerformanceCycles\PerformanceCycleResource;
use App\Models\PerformanceCycle;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPerformanceCycle extends EditRecord
{
    protected static string $resource = PerformanceCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activate')
                ->label('Aktifkan')
                ->icon('heroicon-o-play')
                ->color('success')
                ->visible(fn (PerformanceCycle $record): bool => $record->status === 'draft')
                ->action(function (PerformanceCycle $record): void {
                    $record->update(['status' => 'active']);
                    Notification::make()
                        ->title('Siklus diaktifkan')
                        ->success()
                        ->send();
                }),
            Action::make('review')
                ->label('Mulai Review')
                ->icon('heroicon-o-magnifying-glass')
                ->color('warning')
                ->visible(fn (PerformanceCycle $record): bool => $record->status === 'active')
                ->action(function (PerformanceCycle $record): void {
                    $record->update(['status' => 'review']);
                    Notification::make()
                        ->title('Siklus masuk fase review')
                        ->success()
                        ->send();
                }),
            Action::make('complete')
                ->label('Selesaikan')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->visible(fn (PerformanceCycle $record): bool => $record->status === 'review')
                ->action(function (PerformanceCycle $record): void {
                    $record->update(['status' => 'completed']);
                    Notification::make()
                        ->title('Siklus diselesaikan')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}

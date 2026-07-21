<?php

namespace App\Filament\Resources\PerformanceReviews\Pages;

use App\Filament\Resources\PerformanceReviews\PerformanceReviewResource;
use App\Models\PerformanceReview;
use App\Services\PerformanceService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPerformanceReview extends EditRecord
{
    protected static string $resource = PerformanceReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('start_self_assessment')
                ->label('Mulai Self Assessment')
                ->icon('heroicon-o-user')
                ->color('warning')
                ->visible(fn (PerformanceReview $record): bool => $record->status === 'self_assessment')
                ->url(fn (PerformanceReview $record): string => route('filament.admin.resources.performance-reviews.edit', ['record' => $record->id]))
                ->action(function (PerformanceReview $record): void {
                    Notification::make()
                        ->title('Silakan lakukan self assessment')
                        ->info()
                        ->send();
                }),

            Action::make('calculate_score')
                ->label('Hitung Skor')
                ->icon('heroicon-o-calculator')
                ->color('info')
                ->action(function (PerformanceReview $record): void {
                    $service = app(PerformanceService::class);
                    $score = $service->calculateScore($record);
                    $record->update(['final_score' => $score]);
                    Notification::make()
                        ->title("Skor akhir: {$score}")
                        ->success()
                        ->send();
                }),

            Action::make('complete_review')
                ->label('Selesaikan Review')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (PerformanceReview $record): bool => $record->status !== 'completed')
                ->action(function (PerformanceReview $record): void {
                    $service = app(PerformanceService::class);
                    $score = $service->calculateScore($record);
                    $record->update([
                        'final_score' => $score,
                        'status' => 'completed',
                        'calibration_at' => now(),
                    ]);
                    Notification::make()
                        ->title('Review performa diselesaikan')
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
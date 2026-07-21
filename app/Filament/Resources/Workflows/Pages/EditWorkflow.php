<?php

namespace App\Filament\Resources\Workflows\Pages;

use App\Filament\Resources\Workflows\WorkflowResource;
use App\Models\WorkflowExecution;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\EditRecord;
use App\Services\WorkflowAutomationService;

class EditWorkflow extends EditRecord
{
    protected static string $resource = WorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')
                ->label('Test Workflow')
                ->icon('heroicon-o-play')
                ->color('warning')
                ->action(function () {
                    $workflowService = app(WorkflowAutomationService::class);
                    $context = [
                        'company_id' => $this->record->company_id,
                        'id' => 1,
                        'name' => 'Test Data',
                        'amount' => 1000000,
                        'status' => 'active',
                        'timestamp' => now()->toIso8601String(),
                        'event' => $this->record->trigger_event,
                    ];

                    try {
                        $workflowService->execute($this->record, $context);
                        \Filament\Notifications\Notification::make()
                            ->title('Workflow Dijalankan')
                            ->body('Workflow berhasil dieksekusi.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Workflow Gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Riwayat Eksekusi')
                    ->schema([
                        RepeatableEntry::make('executions')
                            ->label('')
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'success' => 'success',
                                        'error' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('trigger_event')
                                    ->label('Trigger Event'),
                                TextEntry::make('duration_ms')
                                    ->label('Durasi')
                                    ->suffix(' ms'),
                                TextEntry::make('error_message')
                                    ->label('Error')
                                    ->placeholder('-'),
                                TextEntry::make('created_at')
                                    ->label('Waktu')
                                    ->dateTime('d M Y H:i:s'),
                            ])
                            ->columns(5),
                    ])
                    ->collapsible(),
            ]);
    }
}

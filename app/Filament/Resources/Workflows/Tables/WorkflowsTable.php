<?php

namespace App\Filament\Resources\Workflows\Tables;

use App\Services\WorkflowAutomationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkflowsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trigger_event')
                    ->label('Trigger')
                    ->badge()
                    ->color('indigo')
                    ->sortable(),
                TextColumn::make('actions')
                    ->label('Aksi')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' aksi' : '0')
                    ->badge(),
                TextColumn::make('run_count')
                    ->label('Run')
                    ->sortable(),
                TextColumn::make('last_run_at')
                    ->label('Terakhir Run')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum pernah'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('test')
                    ->label('Test')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->action(function ($record) {
                        $workflowService = app(WorkflowAutomationService::class);
                        $context = [
                            'company_id' => $record->company_id,
                            'id' => 1,
                            'name' => 'Test Data',
                            'amount' => 1000000,
                            'status' => 'active',
                            'timestamp' => now()->toIso8601String(),
                            'event' => $record->trigger_event,
                        ];

                        try {
                            $workflowService->execute($record, $context);
                            \Filament\Notifications\Notification::make()
                                ->title('Workflow Dijalankan')
                                ->body('Workflow berhasil dieksekusi dengan data test.')
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
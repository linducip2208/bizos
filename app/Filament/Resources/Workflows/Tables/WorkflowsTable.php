<?php

namespace App\Filament\Resources\Workflows\Tables;

use App\Models\Workflow;
use App\Services\UnifiedWorkflowService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

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
                TextColumn::make('workflow_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => Workflow::types()[$state] ?? ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        Workflow::TYPE_SIMPLE => 'info',
                        Workflow::TYPE_APPROVAL => 'warning',
                        Workflow::TYPE_BPMN => 'success',
                        Workflow::TYPE_AUTOMATION => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('trigger_event')
                    ->label('Trigger')
                    ->badge()
                    ->color('indigo')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('module')
                    ->label('Modul')
                    ->badge()
                    ->color('gray')
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('actions')
                    ->label('Aksi')
                    ->formatStateUsing(fn($state) => is_array($state) ? count($state) . ' aksi' : '0')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('instances_count')
                    ->label('Instance')
                    ->counts([
                        'bpmnInstances' => fn($q) => $q->where('status', 'running'),
                    ])
                    ->formatStateUsing(function ($state, $record) {
                        $running = $record->bpmn_instances_count ?? 0;
                        $pending = $record->approval_requests_count ?? 0;
                        if ($running > 0 || $pending > 0) {
                            return ($running > 0 ? "{$running} BPMN" : '') .
                                ($running > 0 && $pending > 0 ? ', ' : '') .
                                ($pending > 0 ? "{$pending} Approval" : '');
                        }
                        return null;
                    })
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('run_count')
                    ->label('Run')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('last_run_at')
                    ->label('Terakhir Run')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum pernah')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('workflow_type')
                    ->label('Tipe')
                    ->options(Workflow::types()),
                TernaryFilter::make('is_active')
                    ->label('Aktif'),
            ])
            ->recordActions([
                Action::make('test')
                    ->label('Test')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(fn($record) => in_array($record->workflow_type, [Workflow::TYPE_SIMPLE, Workflow::TYPE_AUTOMATION]))
                    ->action(function ($record) {
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
                            $service = app(UnifiedWorkflowService::class);
                            $result = $service->execute($record, $context);
                            \Filament\Notifications\Notification::make()
                                ->title('Workflow Dijalankan')
                                ->body('Workflow berhasil dieksekusi. Status: ' . $result['status'])
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
                Action::make('run')
                    ->label('Jalankan')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->workflow_type === Workflow::TYPE_BPMN)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            $service = app(UnifiedWorkflowService::class);
                            $service->executeBpmn($record, []);
                            \Filament\Notifications\Notification::make()
                                ->title('Proses BPMN Dimulai')
                                ->body('Instance BPMN berhasil dimulai.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Memulai BPMN')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('validate')
                    ->label('Validasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('gray')
                    ->action(function ($record) {
                        $service = app(UnifiedWorkflowService::class);
                        $result = $service->validateWorkflow($record);

                        if ($result['valid']) {
                            \Filament\Notifications\Notification::make()
                                ->title('Workflow Valid')
                                ->body('Semua konfigurasi valid.')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Workflow Tidak Valid')
                                ->body(implode("\n", $result['errors']))
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

<?php

namespace App\Filament\Resources\ApprovalRequests\Pages;

use App\Filament\Resources\ApprovalRequests\ApprovalRequestResource;
use App\Services\ApprovalWorkflowService;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditApprovalRequest extends EditRecord
{
    protected static string $resource = ApprovalRequestResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        $request = $this->getRecord();
        $employeeId = auth()->user()?->employee_id;

        if ($request->isPending() && $employeeId) {
            $service = app(ApprovalWorkflowService::class);

            if ($service->canApprove($request, $employeeId)) {
                $actions[] = Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->form([
                        RichEditor::make('comment')
                            ->label('Catatan')
                            ->toolbarButtons(['bold', 'italic', 'bulletList'])
                            ->required(false),
                    ])
                    ->action(function (array $data) use ($request, $employeeId, $service) {
                        try {
                            $service->approve($request, $employeeId, $data['comment'] ?? null);
                            Notification::make()
                                ->title('Approved')
                                ->success()
                                ->send();
                            $this->redirect($this->getResource()::getUrl('edit', ['record' => $request]));
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    });

                $actions[] = Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->form([
                        RichEditor::make('comment')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->toolbarButtons(['bold', 'italic', 'bulletList']),
                    ])
                    ->action(function (array $data) use ($request, $employeeId, $service) {
                        try {
                            $service->reject($request, $employeeId, $data['comment'] ?? null);
                            Notification::make()
                                ->title('Rejected')
                                ->danger()
                                ->send();
                            $this->redirect($this->getResource()::getUrl('edit', ['record' => $request]));
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    });

                $currentLevel = \App\Models\ApprovalLevel::where('workflow_id', $request->workflow_id)
                    ->where('level', $request->current_level)
                    ->first();

                if ($currentLevel && $currentLevel->can_delegate) {
                    $actions[] = Action::make('delegate')
                        ->label('Delegasikan')
                        ->color('warning')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->form([
                            Select::make('delegate_id')
                                ->label('Delegasikan ke')
                                ->options(
                                    \App\Models\Employee::query()
                                        ->where('id', '!=', $employeeId)
                                        ->where('status', 'active')
                                        ->selectRaw("id, CONCAT(first_name, ' ', last_name) as name")
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->searchable()
                                ->preload()
                                ->required(),
                            RichEditor::make('comment')
                                ->label('Catatan Delegasi')
                                ->toolbarButtons(['bold', 'italic', 'bulletList']),
                        ])
                        ->action(function (array $data) use ($request, $employeeId, $service) {
                            try {
                                $service->delegate($request, $employeeId, $data['delegate_id'], $data['comment'] ?? null);
                                Notification::make()
                                    ->title('Delegated')
                                    ->success()
                                    ->send();
                                $this->redirect($this->getResource()::getUrl('edit', ['record' => $request]));
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        });
                }
            }
        }

        return $actions;
    }
}

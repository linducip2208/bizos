<?php

namespace App\Filament\Resources\ChatbotFlows\Pages;

use App\Filament\Resources\ChatbotFlows\ChatbotFlowResource;
use App\Models\ChatbotFlow;
use App\Services\ChatbotFlowService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditChatbotFlow extends EditRecord
{
    protected static string $resource = ChatbotFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publish')
                ->label('Publikasi')
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->visible(fn (ChatbotFlow $record) => !$record->is_published)
                ->requiresConfirmation()
                ->action(function (ChatbotFlow $record) {
                    $record->publish();
                    Notification::make()
                        ->title('Flow berhasil dipublikasi')
                        ->success()
                        ->send();
                }),

            Action::make('unpublish')
                ->label('Unpublish')
                ->icon('heroicon-o-arrow-down-circle')
                ->color('warning')
                ->visible(fn (ChatbotFlow $record) => $record->is_published)
                ->requiresConfirmation()
                ->action(function (ChatbotFlow $record) {
                    $record->unpublish();
                    Notification::make()
                        ->title('Flow berhasil di-unpublish')
                        ->success()
                        ->send();
                }),

            Action::make('test_flow')
                ->label('Uji Coba')
                ->icon('heroicon-o-beaker')
                ->color('info')
                ->form([
                    TextInput::make('test_message')
                        ->label('Pesan Uji')
                        ->required()
                        ->maxLength(500),
                ])
                ->action(function (ChatbotFlow $record, array $data) {
                    $result = app(ChatbotFlowService::class)->simulateFlow(
                        $record->id,
                        $data['test_message']
                    );

                    Notification::make()
                        ->title('Hasil Uji')
                        ->body("Reply: {$result['reply']}\nAction: {$result['action']}")
                        ->success()
                        ->send();
                }),

            Action::make('flow_nodes')
                ->label('Kelola Node')
                ->icon('heroicon-o-cube-transparent')
                ->color('gray')
                ->url(fn (ChatbotFlow $record) => route('filament.admin.resources.chatbot-flows.edit', $record)),
        ];
    }
}
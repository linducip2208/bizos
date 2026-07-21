<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource\TicketResource;
use App\Models\Employee;
use App\Models\TicketActivity;
use App\Services\HelpdeskService;
use App\Services\TicketTriageService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            Action::make('assign')
                ->label('Assign')
                ->icon('heroicon-o-user')
                ->color('warning')
                ->form([
                    Select::make('assigned_to')
                        ->label('Pilih Staf')
                        ->options(fn () => Employee::where('status', 'active')
                            ->orderBy('first_name')
                            ->get()
                            ->mapWithKeys(fn ($e) => [$e->id => $e->first_name . ' ' . $e->last_name]))
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data): void {
                    $oldAssignee = $this->record->assigned_to;
                    $this->record->update(['assigned_to' => $data['assigned_to']]);

                    TicketActivity::create([
                        'ticket_id' => $this->record->id,
                        'employee_id' => auth()->user()?->employee_id,
                        'activity_type' => 'assigned',
                        'old_value' => (string) $oldAssignee,
                        'new_value' => (string) $data['assigned_to'],
                        'created_at' => now(),
                    ]);

                    Notification::make()->title('Tiket berhasil di-assign')->success()->send();
                }),
            Action::make('change_status')
                ->label('Ubah Status')
                ->icon('heroicon-o-arrows-right-left')
                ->color('info')
                ->form([
                    Select::make('status')
                        ->label('Status Baru')
                        ->options([
                            'open' => 'Terbuka',
                            'in_progress' => 'Dalam Proses',
                            'waiting_on_customer' => 'Menunggu Pelanggan',
                            'resolved' => 'Terselesaikan',
                            'closed' => 'Tertutup',
                        ])
                        ->required()
                        ->default($this->record->status),
                    Textarea::make('note')
                        ->label('Catatan (opsional)')
                        ->rows(2),
                ])
                ->action(function (array $data, HelpdeskService $service): void {
                    $service->changeStatus($this->record, $data['status'], $data['note'] ?? null);
                    Notification::make()->title('Status diubah menjadi ' . $data['status'])->success()->send();
                    redirect(request()->header('Referer'));
                }),
            Action::make('add_reply')
                ->label('Balas')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->form([
                    RichEditor::make('message')
                        ->label('Pesan')
                        ->required(),
                    Select::make('is_internal')
                        ->label('Jenis')
                        ->options([
                            '0' => 'Balasan Publik',
                            '1' => 'Catatan Internal',
                        ])
                        ->default('0')
                        ->required(),
                ])
                ->action(function (array $data, HelpdeskService $service): void {
                    $service->addReply($this->record, [
                        'message' => $data['message'],
                        'is_internal' => (bool) $data['is_internal'],
                        'employee_id' => auth()->user()?->employee_id,
                    ]);
                    Notification::make()->title('Balasan ditambahkan')->success()->send();
                    redirect(request()->header('Referer'));
                }),
            Action::make('escalate')
                ->label('Eskalasi')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (HelpdeskService $service): void {
                    $service->escalate($this->record);
                    Notification::make()->title('Tiket dieskalasi')->success()->send();
                    redirect(request()->header('Referer'));
                }),
            Action::make('suggest_kb')
                ->label('Saran Artikel KB')
                ->icon('heroicon-o-book-open')
                ->color('violet')
                ->action(function (TicketTriageService $triage): void {
                    $articles = $triage->suggestKbArticle($this->record, 3);
                    if (empty($articles)) {
                        Notification::make()->title('Tidak ada artikel yang relevan')->warning()->send();
                        return;
                    }
                    $message = "Artikel Knowledge Base Relevan:\n\n";
                    foreach ($articles as $i => $article) {
                        $message .= ($i + 1) . ". **{$article['title']}** (Skor: {$article['score']})\n{$article['excerpt']}\n\n";
                    }
                    Notification::make()
                        ->title('Ditemukan ' . count($articles) . ' artikel relevan')
                        ->body($message)
                        ->success()
                        ->send();
                }),
            Action::make('suggest_reply')
                ->label('Saran Balasan AI')
                ->icon('heroicon-o-sparkles')
                ->color('indigo')
                ->action(function (TicketTriageService $triage): void {
                    try {
                        $reply = $triage->suggestReply($this->record);
                        Notification::make()
                            ->title('Saran Balasan')
                            ->body($reply)
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()->title('Gagal generate balasan: ' . $e->getMessage())->danger()->send();
                    }
                }),
            Action::make('find_similar')
                ->label('Tiket Serupa')
                ->icon('heroicon-o-magnifying-glass')
                ->color('gray')
                ->action(function (TicketTriageService $triage): void {
                    $similar = $triage->findSimilarResolved($this->record, 5);
                    if (empty($similar)) {
                        Notification::make()->title('Tidak ada tiket serupa yang resolved')->warning()->send();
                        return;
                    }
                    $message = "Tiket Resolved Serupa:\n\n";
                    foreach ($similar as $i => $t) {
                        $message .= ($i + 1) . ". **#{$t['ticket_number']}** - {$t['subject']} (Prioritas: {$t['priority']})\n";
                        $lastReply = \App\Models\Ticket::find($t['id'])?->replies()->where('is_internal', false)->latest()->first();
                        if ($lastReply) {
                            $message .= "   Solusi: " . \Illuminate\Support\Str::limit($lastReply->message, 150) . "\n";
                        }
                        $message .= "\n";
                    }
                    Notification::make()
                        ->title('Ditemukan ' . count($similar) . ' tiket serupa')
                        ->body($message)
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()?->company_id;

        return $data;
    }
}

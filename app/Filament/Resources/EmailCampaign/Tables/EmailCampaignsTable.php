<?php

namespace App\Filament\Resources\EmailCampaign\Tables;

use App\Models\EmailCampaign;
use App\Services\EmailCampaignService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class EmailCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'sending' => 'info',
                        'sent' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'scheduled' => 'Terjadwal',
                        'sending' => 'Mengirim',
                        'sent' => 'Terkirim',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('sent_count')
                    ->label('Terkirim')
                    ->formatStateUsing(fn (EmailCampaign $record): string => "{$record->sent_count}/{$record->total_recipients}")
                    ->sortable(),
                TextColumn::make('open_rate')
                    ->label('Open Rate')
                    ->state(fn (EmailCampaign $record): string => number_format($record->open_rate, 1) . '%')
                    ->sortable(query: function ($query, $direction) {
                        $query->orderByRaw('(opened_count / NULLIF(sent_count, 0)) ' . $direction);
                    }),
                TextColumn::make('scheduled_at')
                    ->label('Jadwal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->label('Terkirim')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('send_now')
                    ->label('Kirim Sekarang')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Kampanye')
                    ->modalDescription(fn (EmailCampaign $record): string => "Kirim kampanye \"{$record->name}\" ke {$record->total_recipients} penerima sekarang?")
                    ->action(function (EmailCampaign $record) {
                        $service = app(EmailCampaignService::class);
                        $service->sendCampaign($record);
                    })
                    ->visible(fn (EmailCampaign $record): bool => in_array($record->status, ['draft', 'scheduled'])),
                Action::make('preview')
                    ->label('Pratinjau')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Pratinjau Email')
                    ->modalContent(fn (EmailCampaign $record): HtmlString => new HtmlString($record->template_content ?? '<p>Tidak ada konten</p>')),
                Action::make('stats')
                    ->label('Statistik')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading(fn (EmailCampaign $record): string => "Statistik: {$record->name}")
                    ->modalContent(function (EmailCampaign $record): HtmlString {
                        $stats = $record->fresh();
                        return new HtmlString(view('filament.marketing.campaign-stats', [
                            'stats' => [
                                'Total Penerima' => $stats->total_recipients,
                                'Terkirim' => $stats->sent_count,
                                'Dibuka' => $stats->opened_count . ' (' . number_format($stats->open_rate, 1) . '%)',
                                'Diklik' => $stats->clicked_count . ' (' . number_format($stats->click_rate, 1) . '%)',
                                'Bounce' => $stats->bounced_count,
                                'Unsubscribed' => $stats->unsubscribed_count,
                            ],
                        ])->render());
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
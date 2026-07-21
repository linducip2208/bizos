<?php

namespace App\Filament\Resources\LandingPage\Tables;

use App\Models\LandingPage;
use App\Services\LandingPageService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LandingPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Slug disalin'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Dipublikasi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('createdBy.full_name')
                    ->label('Dibuat Oleh')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('publish')
                    ->label('Publikasikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Publikasikan Landing Page')
                    ->modalDescription(fn (LandingPage $record): string => "Publikasikan \"{$record->title}\"? Halaman akan dapat diakses publik.")
                    ->action(function (LandingPage $record) {
                        $service = app(LandingPageService::class);
                        $service->publishPage($record);
                    })
                    ->visible(fn (LandingPage $record): bool => $record->status === 'draft'),
                Action::make('preview')
                    ->label('Pratinjau')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (LandingPage $record): string => url('/landing/' . $record->slug))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

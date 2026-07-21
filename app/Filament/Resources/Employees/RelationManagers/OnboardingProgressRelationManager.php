<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Services\EmployeeLifecycleService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OnboardingProgressRelationManager extends RelationManager
{
    protected static string $relationship = 'onboardingProgress';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title = 'Onboarding';
    protected static ?string $label = 'Progress Onboarding';
    protected static ?string $pluralLabel = 'Progress Onboarding';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('checklist.name')
                    ->label('Checklist')
                    ->searchable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Mulai')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->placeholder('Belum selesai'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Selesai',
                        default => $state,
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('start_onboarding')
                    ->label('Mulai Onboarding')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->form([
                        Select::make('checklist_id')
                            ->label('Checklist Onboarding')
                            ->relationship('checklist', 'name', fn ($q) => $q->where('company_id', $this->getOwnerRecord()->company_id))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $service = app(EmployeeLifecycleService::class);
                        $checklist = \App\Models\OnboardingChecklist::find($data['checklist_id']);
                        $service->startOnboarding($this->getOwnerRecord(), $checklist);
                        Notification::make()->title('Onboarding dimulai')->success()->send();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
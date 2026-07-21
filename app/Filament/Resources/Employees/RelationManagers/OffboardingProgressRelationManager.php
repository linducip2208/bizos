<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Services\EmployeeLifecycleService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OffboardingProgressRelationManager extends RelationManager
{
    protected static string $relationship = 'offboardingProgress';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title = 'Offboarding';
    protected static ?string $label = 'Progress Offboarding';
    protected static ?string $pluralLabel = 'Progress Offboarding';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('checklist.name')
                    ->label('Checklist')
                    ->searchable(),
                Tables\Columns\TextColumn::make('resignation_date')
                    ->label('Tgl Resign')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('last_working_date')
                    ->label('Tgl Terakhir Kerja')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('final_settlement_amount')
                    ->label('Final Settlement')
                    ->money('IDR')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('clearance_status')
                    ->label('Clearance')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'it_clear' => 'IT Clear',
                        'finance_clear' => 'Finance Clear',
                        'hr_clear' => 'HR Clear',
                        'asset_clear' => 'Asset Clear',
                        'completed' => 'Selesai',
                        default => $state,
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('start_offboarding')
                    ->label('Mulai Offboarding')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('danger')
                    ->form([
                        Select::make('checklist_id')
                            ->label('Checklist Offboarding')
                            ->relationship('checklist', 'name', fn ($q) => $q->where('company_id', $this->getOwnerRecord()->company_id))
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('resignation_date')
                            ->label('Tanggal Resign')
                            ->required()
                            ->default(now()),
                        DatePicker::make('last_working_date')
                            ->label('Tanggal Terakhir Kerja')
                            ->required()
                            ->default(now()->addMonth()),
                        Textarea::make('reason')
                            ->label('Alasan')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (array $data): void {
                        $service = app(EmployeeLifecycleService::class);
                        $checklist = \App\Models\OffboardingChecklist::find($data['checklist_id']);
                        $progress = $service->startOffboarding(
                            $this->getOwnerRecord(),
                            Carbon::parse($data['resignation_date']),
                            Carbon::parse($data['last_working_date']),
                            $data['reason']
                        );
                        Notification::make()
                            ->title('Offboarding dimulai')
                            ->body('Final settlement: Rp ' . number_format($progress->final_settlement_amount, 0, ',', '.'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
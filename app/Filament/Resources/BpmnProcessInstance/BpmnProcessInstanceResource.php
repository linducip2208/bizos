<?php

namespace App\Filament\Resources\BpmnProcessInstance;

use App\Filament\Resources\BpmnProcessInstance\Pages\ListBpmnProcessInstances;
use App\Filament\Resources\BpmnProcessInstance\Pages\ViewBpmnProcessInstance;
use App\Models\BpmnProcessInstance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\Action;
use App\Filament\Concerns\HasPermissionAccess;

class BpmnProcessInstanceResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = BpmnProcessInstance::class;

    protected static ?string $label = 'Instance BPMN';

    protected static ?string $pluralLabel = 'Instance BPMN';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string|null
    {
        return 'BPMN';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('instance_code')
                    ->label('Kode Instance')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('process.name')
                    ->label('Proses')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'running',
                        'danger' => 'error',
                        'gray' => fn($state) => in_array($state, ['terminated', 'suspended']),
                    ]),
                TextColumn::make('current_element_name')
                    ->label('Elemen Aktif')
                    ->searchable(),
                TextColumn::make('startedBy.name')
                    ->label('Dimulai Oleh'),
                TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->toggleable(),
            ])
            ->defaultSort('started_at', 'desc')
            ->actions([
                ViewAction::make(),
                Action::make('slaStatus')
                    ->label('SLA')
                    ->icon(Heroicon::OutlinedClock)
                    ->action(function (BpmnProcessInstance $record) {
                        $sla = app(\App\Services\BpmnService::class)->getSlaStatus($record->id);
                        \Filament\Notifications\Notification::make()
                            ->title('Status SLA: ' . $sla['overall_sla_status'])
                            ->body('Breached: ' . $sla['breached_count'] . ' tasks')
                            ->color($sla['overall_sla_status'] === 'breached' ? 'danger' : 'success')
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBpmnProcessInstances::route('/'),
            'view' => ViewBpmnProcessInstance::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
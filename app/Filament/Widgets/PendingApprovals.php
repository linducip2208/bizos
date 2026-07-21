<?php

namespace App\Filament\Widgets;

use App\Models\ApprovalRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingApprovals extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected static function isVisibleToRole(?string $role): bool
    {
        return in_array($role, ['manager', 'admin', 'super-admin']);
    }

    public function table(Table $table): Table
    {
        $employeeId = auth()->user()?->employee_id;

        return $table
            ->heading('Approval Tertunda')
            ->description('Pengajuan yang menunggu persetujuan Anda')
            ->query(
                ApprovalRequest::with(['requester', 'workflow'])
                    ->pending()
                    ->when($employeeId, function ($query) use ($employeeId) {
                        return $query->whereHas('workflow.levels', function ($q) use ($employeeId) {
                            $q->where(function ($inner) use ($employeeId) {
                                $inner->where(function ($sub) use ($employeeId) {
                                    $sub->where('approver_type', 'employee')
                                        ->where('approver_id', $employeeId);
                                })
                                ->orWhere(function ($sub) use ($employeeId) {
                                    $sub->where('approver_type', 'role')
                                        ->where('approver_id', auth()->user()?->role_id);
                                })
                                ->orWhere(function ($sub) use ($employeeId) {
                                    $sub->where('approver_type', 'department')
                                        ->where('approver_id', function ($query) use ($employeeId) {
                                            $query->select('department_id')
                                                ->from('employees')
                                                ->where('id', $employeeId);
                                        });
                                })
                                ->orWhere(function ($sub) use ($employeeId) {
                                    $sub->where('approver_type', 'position')
                                        ->where('approver_id', function ($query) use ($employeeId) {
                                            $query->select('position_id')
                                                ->from('employees')
                                                ->where('id', $employeeId);
                                        });
                                });
                            });
                        });
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('module')
                    ->label('Modul')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'leave' => 'Cuti',
                        'reimbursement' => 'Reimbursement',
                        'budget' => 'Budget',
                        'purchase_requisition' => 'Purchase Requisition',
                        'purchase_order' => 'Purchase Order',
                        'overtime' => 'Lembur',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'leave' => 'info',
                        'reimbursement' => 'warning',
                        'budget' => 'success',
                        'purchase_requisition' => 'danger',
                        'purchase_order' => 'primary',
                        'overtime' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('requester.first_name')
                    ->label('Pengaju')
                    ->formatStateUsing(fn ($record) => $record->requester
                        ? $record->requester->first_name . ' ' . $record->requester->last_name
                        : '-'),

                Tables\Columns\TextColumn::make('current_level')
                    ->label('Level')
                    ->formatStateUsing(fn ($record) => "Level {$record->current_level} / {$record->total_levels}")
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sejak')
                    ->since()
                    ->sortable(),
            ])
            ->recordUrl(fn ($record) => \App\Filament\Resources\ApprovalRequests\ApprovalRequestResource::getUrl('edit', ['record' => $record]))
            ->paginated(false);
    }
}

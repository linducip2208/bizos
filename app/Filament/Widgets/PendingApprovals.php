<?php

namespace App\Filament\Widgets;

use App\Models\Leave;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

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
        return $table
            ->heading('Approval Tertunda')
            ->description('Pengajuan cuti, lembur, dan reimbursement yang menunggu approval')
            ->query(
                Leave::with(['employee', 'leaveType'])
                    ->where('status', 'pending')
                    ->select(['id', 'employee_id', 'leave_type_id', 'start_date', 'end_date', 'status', 'created_at'])
                    ->selectRaw("'Cuti' as _type")
            )
            ->columns([
                Tables\Columns\TextColumn::make('_type')
                    ->label('Tipe')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('Karyawan')
                    ->formatStateUsing(fn ($record) => $record->employee?->first_name . ' ' . $record->employee?->last_name),

                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Jenis')
                    ->default('-'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Sampai')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}

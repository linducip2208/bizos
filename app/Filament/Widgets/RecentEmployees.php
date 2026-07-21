<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentEmployees extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Karyawan Terbaru')
            ->description('5 karyawan yang baru bergabung')
            ->query(
                Employee::with(['department', 'position'])
                    ->latest('join_date')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nama')
                    ->formatStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departemen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('position.name')
                    ->label('Jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('join_date')
                    ->label('Tanggal Masuk')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->paginationMode(\Filament\Tables\Enums\PaginationMode::Simple)
            ->paginated(false);
    }
}

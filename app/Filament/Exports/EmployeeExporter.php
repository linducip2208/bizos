<?php

namespace App\Filament\Exports;

use App\Models\Employee;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class EmployeeExporter extends Exporter
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('employee_code')->label('NIP'),
            ExportColumn::make('first_name')->label('Nama Depan'),
            ExportColumn::make('last_name')->label('Nama Belakang'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('phone')->label('Telepon'),
            ExportColumn::make('department.name')->label('Departemen'),
            ExportColumn::make('position.name')->label('Jabatan'),
            ExportColumn::make('join_date')->label('Tanggal Masuk'),
            ExportColumn::make('status')->label('Status'),
        ];
    }
}

<?php

namespace App\Filament\Resources\ApprovalWorkflows\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ApprovalWorkflowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Workflow')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Workflow')
                            ->required()
                            ->maxLength(255),
                        Select::make('module')
                            ->label('Modul')
                            ->options([
                                'leave' => 'Cuti',
                                'overtime' => 'Lembur',
                                'reimbursement' => 'Reimbursement',
                                'budget' => 'Budget',
                                'purchase_requisition' => 'Purchase Requisition',
                                'purchase_order' => 'Purchase Order',
                            ])
                            ->required()
                            ->searchable(),
                        TextInput::make('min_approvers')
                            ->label('Minimal Approver per Level')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->helperText('Jumlah minimum approver yang harus approve di setiap level'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Level Persetujuan')
                    ->description('Urutan level approval dan konfigurasi masing-masing level')
                    ->schema([
                        Repeater::make('levels')
                            ->label('')
                            ->relationship('levels')
                            ->orderColumn('level')
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Level')
                            ->columns(3)
                            ->schema([
                                TextInput::make('level')
                                    ->label('Level')
                                    ->numeric()
                                    ->default(fn ($get) => $get('level') ?? 1)
                                    ->required(),
                                Select::make('approver_type')
                                    ->label('Tipe Approver')
                                    ->options([
                                        'role' => 'Role',
                                        'employee' => 'Karyawan',
                                        'department' => 'Departemen',
                                        'position' => 'Jabatan',
                                    ])
                                    ->required()
                                    ->live(),
                                Select::make('approver_id')
                                    ->label('Approver')
                                    ->options(function ($get) {
                                        return match ($get('approver_type')) {
                                            'role' => \App\Models\Role::pluck('name', 'id')->toArray(),
                                            'employee' => \App\Models\Employee::selectRaw("id, CONCAT(first_name, ' ', last_name) as name")
                                                ->pluck('name', 'id')->toArray(),
                                            'department' => \App\Models\Department::pluck('name', 'id')->toArray(),
                                            'position' => \App\Models\Position::pluck('name', 'id')->toArray(),
                                            default => [],
                                        };
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                Toggle::make('is_required')
                                    ->label('Wajib')
                                    ->default(true),
                                Toggle::make('can_delegate')
                                    ->label('Bisa Delegasi')
                                    ->default(false),
                                TextInput::make('sla_hours')
                                    ->label('SLA (jam)')
                                    ->numeric()
                                    ->nullable()
                                    ->helperText('Kosongkan jika tidak ada SLA'),
                                Select::make('sla_action')
                                    ->label('Aksi SLA')
                                    ->options([
                                        'remind' => 'Ingatkan',
                                        'escalate' => 'Eskalasi',
                                        'auto_approve' => 'Auto Approve',
                                        'auto_reject' => 'Auto Reject',
                                    ])
                                    ->nullable(),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $state['level']
                                ? "Level {$state['level']} - " . ($state['approver_type'] ?? '?')
                                : 'Level Baru'),
                    ]),
            ]);
    }
}
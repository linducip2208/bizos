<?php

namespace App\Filament\Resources\Workflows\Schemas;

use App\Models\Workflow;
use App\Services\UnifiedWorkflowService;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Grid;

class WorkflowForm
{
    public static function configure(Schema $schema): Schema
    {
        $service = app(UnifiedWorkflowService::class);

        $triggerOptions = collect($service->getAvailableTriggers())
            ->mapWithKeys(fn($t) => [$t['event'] => "[{$t['category']}] {$t['label']}"])
            ->toArray();

        $actionOptions = collect($service->getAvailableActions())
            ->mapWithKeys(fn($a) => [$a['type'] => "[{$a['category']}] {$a['label']}"])
            ->toArray();

        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Dasar')
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
                            ->maxLength(255)
                            ->placeholder('cth: Notifikasi Lead Baru'),
                        Select::make('workflow_type')
                            ->label('Tipe Workflow')
                            ->options(Workflow::types())
                            ->required()
                            ->default(Workflow::TYPE_SIMPLE)
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state === Workflow::TYPE_APPROVAL) {
                                    $set('trigger_event', null);
                                }
                                if ($state === Workflow::TYPE_BPMN) {
                                    $set('trigger_event', null);
                                    $set('actions', []);
                                }
                            }),
                        TextInput::make('category')
                            ->label('Kategori')
                            ->nullable()
                            ->maxLength(100)
                            ->placeholder('cth: HR, Finance, Sales'),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->nullable()
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),

                Section::make('Trigger')
                    ->description('Pilih event yang memicu workflow ini (untuk tipe Simple & Automation)')
                    ->visible(fn($get) => in_array($get('workflow_type'), [Workflow::TYPE_SIMPLE, Workflow::TYPE_AUTOMATION]))
                    ->schema([
                        Select::make('trigger_event')
                            ->label('Trigger Event')
                            ->options($triggerOptions)
                            ->searchable()
                            ->required(fn($get) => in_array($get('workflow_type'), [Workflow::TYPE_SIMPLE, Workflow::TYPE_AUTOMATION])),
                    ]),

                Section::make('Kondisi Tambahan')
                    ->description('Tambahkan kondisi filter (opsional). Semua kondisi harus terpenuhi (AND).')
                    ->visible(fn($get) => in_array($get('workflow_type'), [Workflow::TYPE_SIMPLE, Workflow::TYPE_AUTOMATION]))
                    ->schema([
                        Repeater::make('trigger_conditions')
                            ->label('Kondisi')
                            ->schema([
                                TextInput::make('field')
                                    ->label('Field')
                                    ->required()
                                    ->placeholder('cth: amount'),
                                Select::make('operator')
                                    ->label('Operator')
                                    ->options([
                                        '=' => 'Sama dengan (=)',
                                        '!=' => 'Tidak sama (!=)',
                                        '>' => 'Lebih besar (>)',
                                        '<' => 'Lebih kecil (<)',
                                        '>=' => 'Lebih besar/sama (>=)',
                                        '<=' => 'Lebih kecil/sama (<=)',
                                        'contains' => 'Mengandung',
                                        'not_contains' => 'Tidak mengandung',
                                        'in' => 'Termasuk dalam',
                                        'not_in' => 'Tidak termasuk',
                                        'between' => 'Di antara',
                                    ])
                                    ->required(),
                                TextInput::make('value')
                                    ->label('Nilai')
                                    ->required()
                                    ->placeholder('cth: 10000000'),
                            ])
                            ->columns(3)
                            ->default([])
                            ->addActionLabel('Tambah Kondisi'),
                    ]),

                Section::make('Aksi')
                    ->description('Tentukan aksi yang dijalankan saat trigger dan kondisi terpenuhi')
                    ->visible(fn($get) => in_array($get('workflow_type'), [Workflow::TYPE_SIMPLE, Workflow::TYPE_AUTOMATION]))
                    ->schema([
                        Repeater::make('actions')
                            ->label('Daftar Aksi')
                            ->schema([
                                Select::make('type')
                                    ->label('Tipe Aksi')
                                    ->options($actionOptions)
                                    ->required()
                                    ->reactive(),
                                KeyValue::make('config')
                                    ->label('Konfigurasi')
                                    ->hint('Gunakan {{field}} untuk template dari konteks')
                                    ->keyLabel('Key')
                                    ->valueLabel('Value')
                                    ->addActionLabel('Tambah Konfigurasi')
                                    ->helperText('Contoh: {"to": "{{email}}", "subject": "Halo {{name}}!"}'),
                            ])
                            ->columns(1)
                            ->required()
                            ->minItems(1)
                            ->addActionLabel('Tambah Aksi'),
                    ]),

                Section::make('Modul Approval')
                    ->description('Konfigurasi modul yang akan dipasangkan dengan workflow approval')
                    ->visible(fn($get) => $get('workflow_type') === Workflow::TYPE_APPROVAL)
                    ->columns(2)
                    ->schema([
                        Select::make('module')
                            ->label('Modul Target')
                            ->options([
                                'leave' => 'Cuti',
                                'overtime' => 'Lembur',
                                'reimbursement' => 'Reimbursement',
                                'budget' => 'Anggaran',
                                'purchase_requisition' => 'Permintaan Pembelian',
                                'purchase_order' => 'Pesanan Pembelian',
                                'invoice' => 'Invoice',
                                'payment' => 'Pembayaran',
                            ])
                            ->required(fn($get) => $get('workflow_type') === Workflow::TYPE_APPROVAL)
                            ->searchable(),
                        TextInput::make('min_approvers')
                            ->label('Minimal Approver per Level')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->visible(fn($get) => $get('workflow_type') === Workflow::TYPE_APPROVAL),
                        TextInput::make('sla_hours')
                            ->label('SLA Global (jam)')
                            ->numeric()
                            ->nullable()
                            ->suffix('jam')
                            ->helperText('SLA untuk seluruh proses approval'),
                    ]),

                Section::make('Level Persetujuan')
                    ->description('Urutan level approval dan konfigurasi masing-masing level')
                    ->visible(fn($get) => $get('workflow_type') === Workflow::TYPE_APPROVAL)
                    ->schema([
                        Repeater::make('approval_levels')
                            ->label('')
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Level')
                            ->columns(3)
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn(array $state): string => isset($state['level'])
                                ? "Level {$state['level']} - " . ($state['approver_type'] ?? '?')
                                : 'Level Baru')
                            ->schema([
                                TextInput::make('level')
                                    ->label('Level')
                                    ->numeric()
                                    ->default(fn($get) => $get('level') ?? 1)
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
                            ]),
                    ]),

                Section::make('Definisi BPMN')
                    ->description('Import BPMN 2.0 XML untuk visual process designer')
                    ->visible(fn($get) => $get('workflow_type') === Workflow::TYPE_BPMN)
                    ->columns(1)
                    ->schema([
                        Textarea::make('bpmn_xml')
                            ->label('BPMN 2.0 XML')
                            ->rows(12)
                            ->nullable()
                            ->autosize()
                            ->required(fn($get) => $get('workflow_type') === Workflow::TYPE_BPMN)
                            ->helperText('Tempel BPMN 2.0 XML atau gunakan designer visual.')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if (!empty(trim($state ?? ''))) {
                                    try {
                                        $service = app(UnifiedWorkflowService::class);
                                        $parsed = $service->parseBpmnXml($state);
                                        if (!empty($parsed['error'])) {
                                            $set('bpmn_parse_error', $parsed['error']);
                                        } else {
                                            $taskCount = count($parsed['tasks'] ?? []);
                                            $eventCount = count($parsed['events'] ?? []);
                                            $gatewayCount = count($parsed['gateways'] ?? []);
                                            $flowCount = count($parsed['flows'] ?? []);
                                            $set('bpmn_summary', "{$taskCount} task, {$eventCount} event, {$gatewayCount} gateway, {$flowCount} flow");
                                            $set('bpmn_parse_error', null);
                                        }
                                    } catch (\Throwable $e) {
                                        $set('bpmn_parse_error', 'Gagal parsing: ' . $e->getMessage());
                                    }
                                }
                            }),
                        Textarea::make('bpmn_svg')
                            ->label('Diagram SVG Preview')
                            ->rows(3)
                            ->nullable()
                            ->helperText('SVG render dari BPMN diagram (auto-generated oleh designer)'),
                        Placeholder::make('bpmn_summary')
                            ->label('Ringkasan BPMN')
                            ->content(fn($get) => $get('bpmn_summary') ?: '—')
                            ->visible(fn($get) => $get('workflow_type') === Workflow::TYPE_BPMN),
                        Placeholder::make('bpmn_parse_error')
                            ->label('Error Parsing')
                            ->content(fn($get) => $get('bpmn_parse_error'))
                            ->visible(fn($get) => filled($get('bpmn_parse_error')))
                            ->extraAttributes(['class' => 'text-danger-600']),
                        TextInput::make('sla_hours')
                            ->label('SLA Global (jam)')
                            ->numeric()
                            ->nullable()
                            ->suffix('jam')
                            ->visible(fn($get) => $get('workflow_type') === Workflow::TYPE_BPMN),
                    ]),

                Section::make('Konfigurasi Lanjutan')
                    ->description('Pengaturan tambahan untuk workflow')
                    ->visible(fn($get) => in_array($get('workflow_type'), [Workflow::TYPE_SIMPLE, Workflow::TYPE_AUTOMATION]))
                    ->columns(2)
                    ->schema([
                        TextInput::make('webhook_url')
                            ->label('Webhook URL')
                            ->nullable()
                            ->maxLength(200)
                            ->placeholder('Auto-generated')
                            ->disabled(),
                        TextInput::make('schedule_cron')
                            ->label('Schedule Cron')
                            ->nullable()
                            ->maxLength(100)
                            ->placeholder('cth: */30 * * * *')
                            ->helperText('Cron expression untuk eksekusi otomatis'),
                    ]),
            ]);
    }
}

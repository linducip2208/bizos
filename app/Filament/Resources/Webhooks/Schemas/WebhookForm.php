<?php

namespace App\Filament\Resources\Webhooks\Schemas;

use App\Services\WebhookService;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WebhookForm
{
    public static function configure(Schema $schema): Schema
    {
        $triggers = app(WebhookService::class)
            ? collect([
                'employee.created' => 'Employee Created',
                'employee.resigned' => 'Employee Resigned',
                'attendance.late' => 'Attendance Late',
                'attendance.absent' => 'Attendance Absent',
                'attendance.clock_in' => 'Attendance Clock In',
                'leave.submitted' => 'Leave Submitted',
                'leave.approved' => 'Leave Approved',
                'leave.rejected' => 'Leave Rejected',
                'invoice.created' => 'Invoice Created',
                'invoice.paid' => 'Invoice Paid',
                'invoice.overdue' => 'Invoice Overdue',
                'lead.created' => 'Lead Created',
                'lead.converted' => 'Lead Converted',
                'deal.won' => 'Deal Won',
                'deal.lost' => 'Deal Lost',
                'ticket.created' => 'Ticket Created',
                'ticket.closed' => 'Ticket Closed',
                'ticket.breached' => 'Ticket SLA Breached',
                'stock.low_stock' => 'Stock Low',
                'payroll.processed' => 'Payroll Processed',
            ])->toArray()
            : [];

        return $schema
            ->components([
                Section::make('Informasi Webhook')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Webhook')
                            ->required()
                            ->maxLength(255),
                        Select::make('event')
                            ->label('Event')
                            ->options($triggers)
                            ->searchable()
                            ->required(),
                        TextInput::make('url')
                            ->label('Target URL')
                            ->url()
                            ->required()
                            ->maxLength(500)
                            ->placeholder('https://example.com/webhook'),
                        TextInput::make('secret')
                            ->label('Secret Key')
                            ->password()
                            ->nullable()
                            ->maxLength(255)
                            ->helperText('Untuk validasi signature HMAC-SHA256'),
                        TextInput::make('max_retries')
                            ->label('Max Retry')
                            ->numeric()
                            ->default(5)
                            ->minValue(0)
                            ->maxValue(10),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
                Section::make('Custom Headers')
                    ->description('Header tambahan yang dikirim bersama request (opsional)')
                    ->schema([
                        Textarea::make('headers')
                            ->label('Headers (JSON)')
                            ->nullable()
                            ->rows(4)
                            ->helperText('Format JSON. Cth: {"X-Custom-Header": "value"}')
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $state),
                    ]),
            ]);
    }
}

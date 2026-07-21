<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Meeting;
use App\Models\Ticket;
use App\Models\WaConversation;
use Filament\Resources\Pages\Page;
use Carbon\Carbon;

class ViewClient360 extends Page
{
    protected static string $resource = ClientResource::class;

    protected string $view = 'filament.pages.client-360';

    protected static ?string $title = 'Client 360';

    protected static ?int $navigationSort = 505;

    public Client $client;
    public array $clientData = [];
    public array $contacts = [];
    public array $deals = [];
    public array $invoices = [];
    public array $tickets = [];
    public array $waConversations = [];
    public array $meetings = [];
    public array $timeline = [];
    public array $stats = [];

    public function mount(int | string $record): void
    {
        $this->client = Client::with([
            'clientContacts',
            'segments',
            'company',
        ])->findOrFail($record);

        $this->loadData();
    }

    public function loadData(): void
    {
        $this->clientData = $this->client->toArray();
        $this->contacts = $this->client->clientContacts()->get()->toArray();

        $this->deals = Deal::where('client_id', $this->client->id)
            ->with(['stage', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        $this->invoices = Invoice::where('client_id', $this->client->id)
            ->with(['payments'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();

        $this->tickets = Ticket::where('client_id', $this->client->id)
            ->with(['category', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();

        $this->waConversations = WaConversation::whereHas('contact', function ($q) {
            $q->where('client_id', $this->client->id);
        })
            ->orWhere('client_id', $this->client->id)
            ->with(['messages' => function ($q) {
                $q->orderBy('created_at', 'desc')->limit(5);
            }])
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get()
            ->toArray();

        $this->meetings = Meeting::whereHas('client', function ($q) {
            $q->where('client_id', $this->client->id);
        })
            ->orWhere('client_id', $this->client->id)
            ->with(['attendees'])
            ->orderBy('meeting_date', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($meeting) {
                $data = $meeting->toArray();
                $data['meeting_date'] = $meeting->meeting_date?->format('Y-m-d H:i');
                return $data;
            })
            ->toArray();

        $this->stats = [
            'total_deals' => count($this->deals),
            'won_deals' => count(array_filter($this->deals, fn($d) => $d['status'] === 'won')),
            'total_deal_value' => array_sum(array_column($this->deals, 'expected_value')),
            'total_invoices' => count($this->invoices),
            'paid_invoices' => count(array_filter($this->invoices, fn($i) => ($i['status'] ?? '') === 'paid')),
            'total_invoice_value' => array_sum(array_column($this->invoices, 'grand_total')),
            'total_tickets' => count($this->tickets),
            'open_tickets' => count(array_filter($this->tickets, fn($t) => in_array($t['status'] ?? '', ['open', 'in_progress']))),
            'total_meetings' => count($this->meetings),
            'created_at' => $this->client->created_at?->format('d M Y'),
        ];

        $this->buildTimeline();
    }

    protected function buildTimeline(): void
    {
        $timeline = [];

        foreach ($this->deals as $deal) {
            $timeline[] = [
                'type' => 'deal',
                'icon' => 'heroicon-o-currency-dollar',
                'color' => match ($deal['status'] ?? '') {
                    'won' => 'green',
                    'lost' => 'red',
                    default => 'blue',
                },
                'date' => $deal['created_at'] ?? '',
                'title' => 'Deal: ' . ($deal['title'] ?? 'Tanpa Judul'),
                'description' => 'Nilai: Rp ' . number_format((float)($deal['expected_value'] ?? 0), 0, ',', '.'),
                'status' => $deal['status'] ?? '',
                'link' => DealResource::getUrl('edit', ['record' => $deal['id']]) ?? '#',
            ];
        }

        foreach ($this->invoices as $invoice) {
            $timeline[] = [
                'type' => 'invoice',
                'icon' => 'heroicon-o-document-text',
                'color' => match ($invoice['status'] ?? '') {
                    'paid' => 'green',
                    'overdue' => 'red',
                    default => 'yellow',
                },
                'date' => $invoice['created_at'] ?? '',
                'title' => 'Invoice: ' . ($invoice['invoice_number'] ?? ''),
                'description' => 'Rp ' . number_format((float)($invoice['grand_total'] ?? 0), 0, ',', '.'),
                'status' => $invoice['status'] ?? '',
                'link' => '#',
            ];
        }

        foreach ($this->tickets as $ticket) {
            $timeline[] = [
                'type' => 'ticket',
                'icon' => 'heroicon-o-ticket',
                'color' => match ($ticket['status'] ?? '') {
                    'resolved', 'closed' => 'green',
                    'in_progress' => 'blue',
                    default => 'yellow',
                },
                'date' => $ticket['created_at'] ?? '',
                'title' => 'Tiket: ' . ($ticket['subject'] ?? ''),
                'description' => $ticket['status'] ?? '',
                'status' => $ticket['status'] ?? '',
                'link' => '#',
            ];
        }

        foreach ($this->meetings as $meeting) {
            $timeline[] = [
                'type' => 'meeting',
                'icon' => 'heroicon-o-users',
                'color' => 'indigo',
                'date' => $meeting['meeting_date'] ?? '',
                'title' => 'Meeting: ' . ($meeting['title'] ?? 'Rapat'),
                'description' => $meeting['location'] ?? '',
                'status' => $meeting['status'] ?? 'completed',
                'link' => '#',
            ];
        }

        $timeline[] = [
            'type' => 'created',
            'icon' => 'heroicon-o-star',
            'color' => 'gray',
            'date' => $this->client->created_at?->toIso8601String() ?? '',
            'title' => 'Klien dibuat',
            'description' => 'Pertama kali ditambahkan ke sistem',
            'status' => '',
            'link' => '#',
        ];

        usort($timeline, function ($a, $b) {
            return strcmp($b['date'] ?? '', $a['date'] ?? '');
        });

        $this->timeline = $timeline;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'CRM';
    }

    protected function getViewData(): array
    {
        return [
            'client' => $this->client,
            'clientData' => $this->clientData,
            'contacts' => $this->contacts,
            'deals' => $this->deals,
            'invoices' => $this->invoices,
            'tickets' => $this->tickets,
            'waConversations' => $this->waConversations,
            'meetings' => $this->meetings,
            'timeline' => $this->timeline,
            'stats' => $this->stats,
        ];
    }
}
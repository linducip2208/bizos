<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Ticket;
use Filament\Pages\Page;

class Client360 extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?int $navigationSort = 804;

    protected static string $view = 'filament.pages.client-360';

    protected static ?string $title = 'Client 360';

    protected static ?string $navigationLabel = 'Client 360';

    protected static ?string $slug = 'client-360';

    public array $client = [];
    public array $stats = [];

    public static function getNavigationGroup(): ?string
    {
        return '📈 Sales & CRM';
    }

    public function mount(): void
    {
        $clientId = request('client_id');
        $client = $clientId ? Client::find($clientId) : Client::latest()->first();

        if (!$client) {
            $this->client = ['name' => 'Tidak ditemukan'];
            return;
        }

        $this->client = $client->toArray();
        $this->client['client_type'] ??= 'Perusahaan';
        $this->client['industry'] ??= 'Industri tidak diketahui';

        $this->stats = [
            'total_deals' => Deal::where('client_id', $client->id)->count(),
            'won_deals' => Deal::where('client_id', $client->id)->where('status', 'won')->count(),
            'total_invoices' => Invoice::where('client_id', $client->id)->count(),
            'paid_invoices' => Invoice::where('client_id', $client->id)->where('status', 'paid')->count(),
            'total_tickets' => Ticket::where('client_id', $client->id)->count(),
            'open_tickets' => Ticket::where('client_id', $client->id)->where('status', 'open')->count(),
        ];
    }
}

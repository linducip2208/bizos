<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketReply;
use App\Services\HelpdeskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    protected HelpdeskService $helpdesk;

    public function __construct(HelpdeskService $helpdesk)
    {
        $this->helpdesk = $helpdesk;
    }

    public function index(Request $request)
    {
        $clientUser = Auth::guard('client')->user();
        $clientId = $clientUser->client_id;

        $tickets = Ticket::where('client_id', $clientId)
            ->with(['category', 'assignedTo'])
            ->latest('updated_at')
            ->paginate(15);

        return view('client-portal.tickets', compact('clientUser', 'tickets'));
    }

    public function create()
    {
        $clientUser = Auth::guard('client')->user();

        $categories = TicketCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('client-portal.ticket-create', compact('clientUser', 'categories'));
    }

    public function store(Request $request)
    {
        $clientUser = Auth::guard('client')->user();

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'category_id' => ['nullable', 'exists:ticket_categories,id'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        $ticket = $this->helpdesk->createTicket([
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'] ?? null,
            'priority' => $validated['priority'],
            'source' => 'client_portal',
            'client_id' => $clientUser->client_id,
            'contact_id' => null,
            'company_id' => $clientUser->client->company_id ?? null,
        ]);

        return redirect()->route('client.tickets.show', $ticket->id)
            ->with('success', 'Tiket berhasil dibuat.');
    }

    public function show($id)
    {
        $clientUser = Auth::guard('client')->user();

        $ticket = Ticket::where('client_id', $clientUser->client_id)
            ->with(['replies' => function ($q) {
                $q->where('is_internal', false)->with(['employee', 'user'])->latest();
            }, 'category', 'assignedTo', 'attachments'])
            ->findOrFail($id);

        return view('client-portal.ticket-show', compact('clientUser', 'ticket'));
    }

    public function reply(Request $request, $id)
    {
        $clientUser = Auth::guard('client')->user();

        $ticket = Ticket::where('client_id', $clientUser->client_id)->findOrFail($id);

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $this->helpdesk->addReply($ticket, [
            'message' => $validated['message'],
            'user_id' => null,
            'is_internal' => false,
        ]);

        return redirect()->route('client.tickets.show', $ticket->id)
            ->with('success', 'Balasan dikirim.');
    }
}

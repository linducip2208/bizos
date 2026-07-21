<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientContact;
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

    public function index()
    {
        $user = Auth::user();

        $clientIds = ClientContact::where('email', $user->email)
            ->pluck('client_id')
            ->toArray();

        $tickets = Ticket::whereIn('client_id', $clientIds)
            ->with(['category', 'assignedTo'])
            ->latest('updated_at')
            ->paginate(15);

        return view('portal.tickets-index', compact('tickets'));
    }

    public function create()
    {
        $categories = TicketCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('portal.tickets-create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'category_id' => ['nullable', 'exists:ticket_categories,id'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        $user = Auth::user();
        $contact = ClientContact::where('email', $user->email)->first();

        $ticket = $this->helpdesk->createTicket([
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'] ?? null,
            'priority' => $validated['priority'],
            'source' => 'portal',
            'client_id' => $contact?->client_id,
            'contact_id' => $contact?->id,
            'company_id' => $user->company_id,
        ]);

        return redirect()->route('portal.tickets.show', $ticket->id)
            ->with('success', 'Tiket berhasil dibuat.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $clientIds = ClientContact::where('email', $user->email)
            ->pluck('client_id')
            ->toArray();

        $ticket = Ticket::whereIn('client_id', $clientIds)
            ->with(['replies' => function ($q) {
                $q->where('is_internal', false)->with(['employee', 'user'])->latest();
            }, 'category', 'assignedTo', 'attachments', 'activities' => function ($q) {
                $q->latest('created_at')->limit(20);
            }])
            ->findOrFail($id);

        return view('portal.tickets-show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $user = Auth::user();
        $clientIds = ClientContact::where('email', $user->email)
            ->pluck('client_id')
            ->toArray();

        $ticket = Ticket::whereIn('client_id', $clientIds)->findOrFail($id);

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $this->helpdesk->addReply($ticket, [
            'message' => $validated['message'],
            'user_id' => $user->id,
            'is_internal' => false,
        ]);

        if ($ticket->status === 'resolved' || $ticket->status === 'closed') {
            $this->helpdesk->changeStatus($ticket, 'open');
        }

        return redirect()->route('portal.tickets.show', $ticket->id)
            ->with('success', 'Balasan dikirim.');
    }
}

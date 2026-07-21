<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::query()->with('category', 'assignedTo');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['ticket_number', 'subject', 'status', 'priority', 'category_id', 'assigned_to']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Ticket::query()->with('category', 'assignedTo', 'replies', 'attachments', 'slaPolicy');
        $this->applyCompanyScope($query, $request);
        $ticket = $query->find($id);

        if (! $ticket) {
            return $this->error('Tiket tidak ditemukan.', 404);
        }

        return $this->success($ticket);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'category_id' => 'nullable|exists:ticket_categories,id',
            'assigned_to' => 'nullable|exists:employees,id',
            'subject' => 'required|string|max:500',
            'description' => 'required|string|max:5000',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:20',
        ]);

        $ticket = Ticket::create($validated + ['ticket_number' => 'TKT-' . date('Ymd') . '-' . str_pad((Ticket::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT)]);

        return $this->success($ticket, 'Tiket berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Ticket::query();
        $this->applyCompanyScope($query, $request);
        $ticket = $query->find($id);

        if (! $ticket) {
            return $this->error('Tiket tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:employees,id',
            'subject' => 'nullable|string|max:500',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:open,in_progress,waiting_on_customer,resolved,closed',
        ]);

        $ticket->update($validated);

        return $this->success($ticket, 'Tiket berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Ticket::query();
        $this->applyCompanyScope($query, $request);
        $ticket = $query->find($id);

        if (! $ticket) {
            return $this->error('Tiket tidak ditemukan.', 404);
        }

        $ticket->delete();

        return $this->success(null, 'Tiket berhasil dihapus.');
    }
}

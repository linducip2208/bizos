<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Journal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JournalApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Journal::query()->with('entries');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['journal_date', 'journal_type', 'status']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Journal::query()->with('entries.coa');
        $this->applyCompanyScope($query, $request);
        $journal = $query->find($id);

        if (! $journal) {
            return $this->error('Jurnal tidak ditemukan.', 404);
        }

        return $this->success($journal);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'journal_date' => 'required|date',
            'description' => 'required|string|max:1000',
            'journal_type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:20',
        ]);

        $journal = Journal::create($validated);

        if ($request->has('entries')) {
            foreach ($request->entries as $entry) {
                $journal->entries()->create([
                    'coa_id' => $entry['coa_id'],
                    'debit' => $entry['debit'] ?? 0,
                    'credit' => $entry['credit'] ?? 0,
                    'description' => $entry['description'] ?? '',
                ]);
            }
        }

        return $this->success($journal->load('entries'), 'Jurnal berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Journal::query();
        $this->applyCompanyScope($query, $request);
        $journal = $query->find($id);

        if (! $journal) {
            return $this->error('Jurnal tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|string|max:20',
        ]);

        $journal->update($validated);

        return $this->success($journal, 'Jurnal berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Journal::query();
        $this->applyCompanyScope($query, $request);
        $journal = $query->find($id);

        if (! $journal) {
            return $this->error('Jurnal tidak ditemukan.', 404);
        }

        $journal->entries()->delete();
        $journal->delete();

        return $this->success(null, 'Jurnal berhasil dihapus.');
    }
}

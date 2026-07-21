<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ApiBaseController extends Controller
{
    protected function success($data = null, string $message = 'Berhasil', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error(string $message = 'Gagal', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function paginatedResponse($query, Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $page = (int) $request->query('page', 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil',
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    protected function applyCompanyScope(Builder $query, Request $request): Builder
    {
        $companyId = $request->attributes->get('company_id');
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        return $query;
    }

    protected function applyFilters(Builder $query, Request $request, array $allowedFilters = []): Builder
    {
        foreach ($allowedFilters as $filter) {
            if ($request->has($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        if ($request->has('search') && ! empty($allowedFilters)) {
            $search = $request->query('search');
            $query->where(function (Builder $q) use ($search, $allowedFilters) {
                foreach ($allowedFilters as $filter) {
                    $q->orWhere($filter, 'like', "%{$search}%");
                }
            });
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $dateField = $request->query('date_field', 'created_at');
            $query->whereBetween($dateField, [
                $request->query('date_from'),
                $request->query('date_to'),
            ]);
        }

        if ($request->has('sort_by')) {
            $direction = $request->query('sort_dir', 'desc');
            $query->orderBy($request->query('sort_by'), $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }
}

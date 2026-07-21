<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OfflineService;
use Illuminate\Http\Request;

class OfflineController extends Controller
{
    protected OfflineService $offlineService;

    public function __construct(OfflineService $offlineService)
    {
        $this->offlineService = $offlineService;
    }

    public function sync(Request $request)
    {
        $request->validate([
            'actions' => ['required', 'array', 'max:500'],
            'actions.*.action_type' => ['required', 'string'],
            'actions.*.action_data' => ['required', 'array'],
            'actions.*.client_id' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $userId = $user->id;

        $receivedCount = 0;
        foreach ($request->actions as $actionData) {
            try {
                $this->offlineService->queueAction(
                    $actionData['action_type'],
                    $actionData['action_data'],
                    $userId
                );
                $receivedCount++;
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }

        $syncResult = $this->offlineService->syncAll($userId);

        $updates = $this->getServerUpdates($userId, $request->get('last_sync_at'));

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi selesai.',
            'data' => [
                'actions_received' => $receivedCount,
                'actions_synced' => $syncResult['synced_count'],
                'actions_failed' => $syncResult['failed_count'],
                'errors' => $syncResult['errors'],
                'server_updates' => $updates,
                'sync_timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function offlineData(Request $request)
    {
        $user = $request->user();
        $data = $this->offlineService->getOfflineData($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Data offline berhasil diunduh.',
            'data' => $data,
        ]);
    }

    public function checkUpdates(Request $request)
    {
        $request->validate([
            'since' => ['required', 'date'],
            'data_types' => ['nullable', 'array'],
        ]);

        $user = $request->user();
        $dataTypes = $request->get('data_types', ['employees', 'products', 'warehouses', 'tasks', 'shifts', 'settings']);
        $lastSyncAt = $request->get('since');

        $needsRefresh = [];
        foreach ($dataTypes as $type) {
            $needsRefresh[$type] = $this->offlineService->needsRefresh($user->id, $type, $lastSyncAt);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengecekan update selesai.',
            'data' => [
                'needs_refresh' => $needsRefresh,
                'any_changes' => in_array(true, $needsRefresh),
                'checked_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function pendingActions(Request $request)
    {
        $user = $request->user();
        $pending = \App\Models\OfflineAction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $failed = \App\Models\OfflineAction::where('user_id', $user->id)
            ->where('status', 'failed')
            ->count();

        $conflict = \App\Models\OfflineAction::where('user_id', $user->id)
            ->where('status', 'conflict')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Status offline actions.',
            'data' => [
                'pending' => $pending,
                'failed' => $failed,
                'conflict' => $conflict,
                'total' => $pending + $failed + $conflict,
            ],
        ]);
    }

    public function resolveConflict(Request $request)
    {
        $request->validate([
            'action_id' => ['required', 'integer', 'exists:offline_actions,id'],
            'resolution' => ['required', 'string', 'in:use_local,use_server,merge'],
        ]);

        $user = $request->user();
        $action = \App\Models\OfflineAction::where('id', $request->action_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $this->offlineService->resolveConflict($action, $request->resolution);

        return response()->json([
            'success' => true,
            'message' => 'Konflik berhasil diselesaikan.',
            'data' => [
                'action_id' => $action->id,
                'resolution' => $request->resolution,
                'status' => $action->fresh()->status,
            ],
        ]);
    }

    protected function getServerUpdates(int $userId, ?string $lastSyncAt): array
    {
        if (!$lastSyncAt) {
            return ['full_data' => true, 'message' => 'Gunakan /api/v1/mobile/offline-data untuk data lengkap.'];
        }

        $updates = [];
        $since = \Carbon\Carbon::parse($lastSyncAt);

        $notifications = \App\Models\Notification::where('user_id', $userId)
            ->where('created_at', '>', $since)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $updates['notifications'] = [
            'count' => $notifications->count(),
            'items' => $notifications->toArray(),
        ];

        $tasks = \App\Models\Task::whereHas('assignees', function ($q) use ($userId) {
            $employee = \App\Models\Employee::whereHas('user', fn($sub) => $sub->where('id', $userId))->first();
            if ($employee) {
                $q->where('employee_id', $employee->id);
            }
        })
            ->where('updated_at', '>', $since)
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get(['id', 'title', 'status', 'priority', 'updated_at']);

        $updates['tasks'] = [
            'count' => $tasks->count(),
            'items' => $tasks->toArray(),
        ];

        return $updates;
    }
}

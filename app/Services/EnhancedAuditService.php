<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class EnhancedAuditService
{
    protected array $ignoredFields = ['updated_at', 'last_login_at', 'last_login_ip', 'remember_token'];

    public function log(string $action, Model $model, ?array $changes = null): AuditLog
    {
        $oldValues = null;
        $newValues = $changes;

        if ($action === 'updated' && $model->wasChanged()) {
            $original = $model->getOriginal();
            $dirty = $model->getDirty();

            $oldValues = [];
            $newValues = [];

            foreach ($dirty as $key => $newValue) {
                if (in_array($key, $this->ignoredFields)) {
                    continue;
                }

                $oldValues[$key] = $original[$key] ?? null;
                $newValues[$key] = $newValue;
            }

            $changes = null;
        }

        if ($action === 'created') {
            $newValues = $model->getAttributes();
            $newValues = array_diff_key($newValues, array_flip($this->ignoredFields));
        }

        if ($action === 'deleted') {
            $oldValues = $model->getAttributes();
            $oldValues = array_diff_key($oldValues, array_flip($this->ignoredFields));
        }

        return AuditLog::create([
            'user_id' => auth()->id(),
            'company_id' => $model->company_id ?? auth()->user()?->company_id,
            'action' => $action,
            'action_type' => $this->mapActionToType($action),
            'entity_type' => get_class($model),
            'entity_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues ?? $changes,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'restorable' => in_array($action, ['updated', 'deleted']),
            'metadata' => [
                'url' => Request::fullUrl(),
                'method' => Request::method(),
                'timestamp' => now()->toISOString(),
            ],
            'created_at' => now(),
        ]);
    }

    public function getModelHistory(Model $model): Collection
    {
        return AuditLog::where('entity_type', get_class($model))
            ->where('entity_id', $model->getKey())
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();
    }

    public function getFieldHistory(Model $model, string $field): Collection
    {
        return AuditLog::where('entity_type', get_class($model))
            ->where('entity_id', $model->getKey())
            ->where(function ($query) use ($field) {
                $query->whereNotNull("old_values->{$field}")
                    ->orWhereNotNull("new_values->{$field}");
            })
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();
    }

    public function restoreVersion(AuditLog $log): ?Model
    {
        if (!$log->restorable) {
            throw new \RuntimeException('Log ini tidak dapat direstore.');
        }

        $modelClass = $log->entity_type;

        if (!class_exists($modelClass)) {
            throw new \RuntimeException("Model class '{$modelClass}' tidak ditemukan.");
        }

        $model = $modelClass::withTrashed()->find($log->entity_id);

        if (!$model) {
            $model = new $modelClass();
            $model->setAttribute($model->getKeyName(), $log->entity_id);
            $model->exists = false;
        }

        $oldValues = $log->old_values ?? [];

        if (!empty($oldValues)) {
            foreach ($oldValues as $key => $value) {
                if ($model->isFillable($key)) {
                    $model->setAttribute($key, $value);
                }
            }
        }

        if ($model->exists && $model->trashed()) {
            $model->restore();
        }

        $model->save();

        $this->log('restored', $model, [
            'restored_from_audit_log_id' => $log->id,
            'restored_values' => $oldValues,
        ]);

        return $model;
    }

    public function getTodaySummary(): array
    {
        $today = now()->startOfDay();

        return [
            'total_logs' => AuditLog::whereDate('created_at', $today)->count(),
            'by_action' => AuditLog::whereDate('created_at', $today)
                ->select('action_type', DB::raw('count(*) as total'))
                ->groupBy('action_type')
                ->pluck('total', 'action_type')
                ->toArray(),
            'by_user' => AuditLog::whereDate('created_at', $today)
                ->with('user')
                ->get()
                ->groupBy('user.name')
                ->map(fn ($logs) => $logs->count())
                ->sortDesc()
                ->take(10)
                ->toArray(),
            'top_entities' => AuditLog::whereDate('created_at', $today)
                ->select('entity_type', DB::raw('count(*) as total'))
                ->groupBy('entity_type')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'entity_type')
                ->toArray(),
        ];
    }

    protected function mapActionToType(string $action): string
    {
        return match ($action) {
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'deleted',
            'restored' => 'restored',
            'approved' => 'approved',
            'rejected' => 'rejected',
            'login' => 'login',
            'logout' => 'logout',
            'export' => 'export',
            'import' => 'import',
            default => $action,
        };
    }
}

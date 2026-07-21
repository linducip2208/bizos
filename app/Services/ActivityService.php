<?php

namespace App\Services;

use App\Models\ActivityTimeline;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ActivityService
{
    public static function log(string $action, Model $model, ?int $userId = null, array $meta = []): void
    {
        $companyId = null;
        if (method_exists($model, 'company') || $model->getAttribute('company_id')) {
            $companyId = $model->getAttribute('company_id');
        } elseif (Auth::check()) {
            $companyId = Auth::user()->company_id;
        }

        $description = self::buildDescription($action, $model);

        ActivityTimeline::create([
            'company_id' => $companyId,
            'user_id' => $userId ?? (Auth::check() ? Auth::id() : null),
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
            'action' => $action,
            'description' => $description,
            'metadata' => !empty($meta) ? $meta : null,
        ]);
    }

    protected static function buildDescription(string $action, Model $model): string
    {
        $modelName = class_basename($model);
        $identifier = self::getModelIdentifier($model);

        $descriptions = [
            'created' => "{$modelName} \"{$identifier}\" dibuat",
            'updated' => "{$modelName} \"{$identifier}\" diperbarui",
            'deleted' => "{$modelName} \"{$identifier}\" dihapus",
            'status_changed' => "Status {$modelName} \"{$identifier}\" berubah",
            'approved' => "{$modelName} \"{$identifier}\" disetujui",
            'rejected' => "{$modelName} \"{$identifier}\" ditolak",
        ];

        return $descriptions[$action] ?? "{$modelName} \"{$identifier}\" {$action}";
    }

    protected static function getModelIdentifier(Model $model): string
    {
        if ($model->getAttribute('name')) {
            return $model->getAttribute('name');
        }
        if ($model->getAttribute('title')) {
            return $model->getAttribute('title');
        }
        if ($model->getAttribute('code')) {
            return $model->getAttribute('code');
        }
        if ($model->getAttribute('employee_code')) {
            return $model->getAttribute('employee_code');
        }
        return (string) $model->getKey();
    }

    public function getUserFeed(int $userId, int $limit = 50): Collection
    {
        return ActivityTimeline::with(['user', 'model'])
            ->where('user_id', $userId)
            ->recent($limit)
            ->get();
    }

    public function getTodaySummary(int $companyId): array
    {
        $today = ActivityTimeline::where('company_id', $companyId)
            ->whereDate('created_at', now()->toDateString())
            ->get();

        return [
            'total' => $today->count(),
            'created' => $today->where('action', 'created')->count(),
            'updated' => $today->where('action', 'updated')->count(),
            'deleted' => $today->where('action', 'deleted')->count(),
            'approved' => $today->where('action', 'approved')->count(),
            'rejected' => $today->where('action', 'rejected')->count(),
            'items' => $today->take(20)->toArray(),
        ];
    }

    public function getCompanyFeed(int $companyId, int $limit = 50): Collection
    {
        return ActivityTimeline::with(['user', 'model'])
            ->forCompany($companyId)
            ->recent($limit)
            ->get();
    }
}

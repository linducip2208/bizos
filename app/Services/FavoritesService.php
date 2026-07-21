<?php

namespace App\Services;

use App\Models\RecentlyViewed;
use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Support\Collection;

class FavoritesService
{
    public function toggleFavorite(User $user, string $resourceType, string $label, string $url, string $icon): bool
    {
        $existing = UserFavorite::where('user_id', $user->id)
            ->where('resource_type', $resourceType)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        $maxSort = UserFavorite::where('user_id', $user->id)->max('sort_order') ?? 0;

        UserFavorite::create([
            'user_id' => $user->id,
            'resource_type' => $resourceType,
            'resource_label' => $label,
            'resource_url' => $url,
            'resource_icon' => $icon,
            'sort_order' => $maxSort + 1,
        ]);

        return true;
    }

    public function getFavorites(User $user): Collection
    {
        return UserFavorite::where('user_id', $user->id)
            ->orderBy('sort_order')
            ->get();
    }

    public function trackRecentlyViewed(User $user, string $type, string $label, string $url, string $icon): void
    {
        RecentlyViewed::where('user_id', $user->id)
            ->where('resource_type', $type)
            ->delete();

        RecentlyViewed::create([
            'user_id' => $user->id,
            'resource_type' => $type,
            'resource_label' => $label,
            'resource_url' => $url,
            'resource_icon' => $icon,
            'viewed_at' => now(),
        ]);

        $count = RecentlyViewed::where('user_id', $user->id)->count();
        if ($count > 20) {
            $idsToDelete = RecentlyViewed::where('user_id', $user->id)
                ->orderBy('viewed_at', 'asc')
                ->limit($count - 20)
                ->pluck('id');

            RecentlyViewed::whereIn('id', $idsToDelete)->delete();
        }
    }

    public function getRecentlyViewed(User $user): Collection
    {
        return RecentlyViewed::where('user_id', $user->id)
            ->orderBy('viewed_at', 'desc')
            ->limit(10)
            ->get();
    }
}

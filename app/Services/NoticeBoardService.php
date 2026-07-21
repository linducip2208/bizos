<?php

namespace App\Services;

use App\Models\NoticeBoardPost;
use App\Models\NoticeBoardRead;
use Illuminate\Support\Collection;

class NoticeBoardService
{
    public function getActive(): Collection
    {
        return NoticeBoardPost::active()
            ->with('postedBy')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    public function markAsRead(int $postId, int $userId): void
    {
        NoticeBoardRead::updateOrCreate(
            ['post_id' => $postId, 'user_id' => $userId],
            ['read_at' => now()]
        );
    }

    public function getUnreadCount(int $userId): int
    {
        $totalActive = NoticeBoardPost::active()->count();
        $readCount = NoticeBoardRead::where('user_id', $userId)
            ->whereHas('post', fn($q) => $q->active())
            ->count();

        return max(0, $totalActive - $readCount);
    }

    public function getUnreadPosts(int $userId): Collection
    {
        $readIds = NoticeBoardRead::where('user_id', $userId)->pluck('post_id');

        return NoticeBoardPost::active()
            ->whereNotIn('id', $readIds)
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }
}

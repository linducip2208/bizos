<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\NoticeBoardService;

class NoticeBoard extends Component
{
    public array $notices = [];
    public int $unreadCount = 0;
    public ?int $expandedId = null;

    protected NoticeBoardService $noticeBoardService;

    public function boot(NoticeBoardService $noticeBoardService): void
    {
        $this->noticeBoardService = $noticeBoardService;
    }

    public function mount(): void
    {
        $this->loadNotices();
    }

    public function loadNotices(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $this->notices = $this->noticeBoardService->getActive()->take(5)->toArray();
        $this->unreadCount = $this->noticeBoardService->getUnreadCount($user->id);
    }

    public function toggleExpand(int $postId): void
    {
        $user = auth()->user();
        $this->noticeBoardService->markAsRead($postId, $user->id);
        $this->expandedId = $this->expandedId === $postId ? null : $postId;
        $this->loadNotices();
    }

    public function render()
    {
        return view('livewire.notice-board');
    }
}

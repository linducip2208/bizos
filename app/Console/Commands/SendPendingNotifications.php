<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendPendingNotifications extends Command
{
    protected $signature = 'notifications:send-pending';

    protected $description = 'Send unsent notifications and mark their sent_at timestamp.';

    public function handle(): int
    {
        $pending = Notification::whereNull('sent_at')
            ->where('is_read', false)
            ->limit(100)
            ->get();

        $count = 0;

        foreach ($pending as $notification) {
            $success = $this->dispatchNotification($notification);

            if ($success) {
                $notification->update(['sent_at' => now()]);
                $count++;
            }
        }

        $this->info("{$count} notification(s) sent.");

        return self::SUCCESS;
    }

    protected function dispatchNotification(Notification $notification): bool
    {
        $channel = $notification->channel ?? 'in_app';

        if ($channel === 'in_app' || $channel === 'database') {
            NotificationService::send(
                $notification->user_id,
                $notification->notification_type,
                $notification->title,
                $notification->body,
                $channel,
                $notification->data,
            );
            return true;
        }

        if ($channel === 'email' && $notification->user?->email) {
            NotificationService::send(
                $notification->user_id,
                $notification->notification_type,
                $notification->title,
                $notification->body,
                $channel,
                $notification->data,
            );
            return true;
        }

        if ($channel === 'whatsapp') {
            NotificationService::send(
                $notification->user_id,
                $notification->notification_type,
                $notification->title,
                $notification->body,
                $channel,
                $notification->data,
            );
            return true;
        }

        return false;
    }
}

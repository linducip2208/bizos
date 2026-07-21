<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function send(int $userId, string $type, string $title, string $body, string $channel = 'in_app', ?array $data = null): void
    {
        Notification::create([
            'user_id' => $userId,
            'notification_type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'channel' => $channel,
            'sent_at' => now(),
        ]);
    }
}

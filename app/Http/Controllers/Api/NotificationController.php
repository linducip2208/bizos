<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(20);

        $data = $notifications->through(function ($n) {
            return [
                'id' => $n->id,
                'title' => $n->title,
                'body' => $n->body,
                'type' => $n->notification_type,
                'data' => $n->data,
                'is_read' => $n->is_read,
                'read_at' => $n->read_at?->format('Y-m-d H:i:s'),
                'created_at' => $n->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json($data);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['message' => 'Notifikasi ditandai sebagai telah dibaca.']);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $user->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['message' => 'Semua notifikasi ditandai sebagai telah dibaca.']);
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $count = $user->notifications()
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}

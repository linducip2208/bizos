<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VoiceNote;
use App\Models\VoiceNoteRecipient;
use App\Services\VoiceNoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VoiceNoteController extends Controller
{
    protected VoiceNoteService $voiceNoteService;

    public function __construct(VoiceNoteService $voiceNoteService)
    {
        $this->voiceNoteService = $voiceNoteService;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'audio' => ['required', 'file', 'mimes:mp3,wav,ogg,webm,m4a,aac', 'max:20480'],
            'recipient_ids' => ['required', 'array', 'min:1', 'max:50'],
            'recipient_ids.*' => ['integer', 'exists:users,id'],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:600'],
            'title' => ['nullable', 'string', 'max:200'],
        ]);

        $user = $request->user();
        $file = $request->file('audio');
        $audioPath = $this->voiceNoteService->storeUploadedFile($file, $user->id);

        $voiceNote = $this->voiceNoteService->upload(
            $user->id,
            $request->recipient_ids,
            $audioPath,
            (int) $request->get('duration_seconds', 0)
        );

        if ($request->filled('title')) {
            $voiceNote->update(['title' => $request->title]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voice note berhasil diunggah.',
            'data' => [
                'id' => $voiceNote->id,
                'audio_url' => route('api.mobile.voice-note.audio', ['id' => $voiceNote->id]),
                'duration_seconds' => $voiceNote->duration_seconds,
                'recipient_count' => count($request->recipient_ids),
                'created_at' => $voiceNote->created_at->toIso8601String(),
            ],
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $type = $request->get('type', 'all');
        $perPage = min((int) $request->get('per_page', 20), 50);

        $result = $this->voiceNoteService->getVoiceNotes($user->id, $type, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar voice notes.',
            'data' => $result['data'],
            'pagination' => $result['pagination'],
        ]);
    }

    public function audio(Request $request, int $id)
    {
        $user = $request->user();
        $voiceNote = VoiceNote::find($id);

        if (!$voiceNote) {
            return response()->json([
                'success' => false,
                'message' => 'Voice note tidak ditemukan.',
            ], 404);
        }

        $isSender = $voiceNote->sender_id === $user->id;
        $isRecipient = VoiceNoteRecipient::where('voice_note_id', $id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isSender && !$isRecipient) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke voice note ini.',
            ], 403);
        }

        $audioPath = $voiceNote->audio_path;
        $fullPath = storage_path('app/public/' . $audioPath);

        if (!file_exists($fullPath)) {
            return response()->json([
                'success' => false,
                'message' => 'File audio tidak ditemukan.',
            ], 404);
        }

        $mimeType = mime_content_type($fullPath) ?: 'audio/mpeg';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="voice-note-' . $voiceNote->id . '.' . pathinfo($audioPath, PATHINFO_EXTENSION) . '"',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function markPlayed(Request $request, int $id)
    {
        $voiceNote = VoiceNote::find($id);

        if (!$voiceNote) {
            return response()->json([
                'success' => false,
                'message' => 'Voice note tidak ditemukan.',
            ], 404);
        }

        $user = $request->user();
        $this->voiceNoteService->markPlayed($voiceNote, $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Voice note ditandai sudah diputar.',
        ]);
    }

    public function transcribe(Request $request, int $id)
    {
        $voiceNote = VoiceNote::find($id);

        if (!$voiceNote) {
            return response()->json([
                'success' => false,
                'message' => 'Voice note tidak ditemukan.',
            ], 404);
        }

        $user = $request->user();
        $isSender = $voiceNote->sender_id === $user->id;
        $isRecipient = VoiceNoteRecipient::where('voice_note_id', $id)->where('user_id', $user->id)->exists();

        if (!$isSender && !$isRecipient) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke voice note ini.',
            ], 403);
        }

        $transcript = $this->voiceNoteService->transcribe($voiceNote);

        return response()->json([
            'success' => true,
            'message' => 'Hasil transkripsi.',
            'data' => [
                'transcript' => $transcript,
                'voice_note_id' => $voiceNote->id,
            ],
        ]);
    }

    public function unplayedCount(Request $request)
    {
        $user = $request->user();
        $count = $this->voiceNoteService->getUnplayedCount($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Jumlah voice note belum diputar.',
            'data' => [
                'unplayed_count' => $count,
            ],
        ]);
    }

    public function createChannel(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'member_ids' => ['required', 'array', 'min:1', 'max:100'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $channel = $this->voiceNoteService->createChannel(
            $request->name,
            $request->member_ids
        );

        return response()->json([
            'success' => true,
            'message' => 'Channel PTT berhasil dibuat.',
            'data' => $channel,
        ]);
    }

    public function broadcastChannel(Request $request, int $channelId)
    {
        $request->validate([
            'audio' => ['required', 'file', 'mimes:mp3,wav,ogg,webm,m4a,aac', 'max:10240'],
        ]);

        $user = $request->user();
        $channel = \App\Models\VoiceChannel::find($channelId);

        if (!$channel || !$channel->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Channel tidak ditemukan atau tidak aktif.',
            ], 404);
        }

        $isMember = \App\Models\VoiceChannelMember::where('voice_channel_id', $channelId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return response()->json([
                'success' => false,
                'message' => 'Anda bukan anggota channel ini.',
            ], 403);
        }

        $file = $request->file('audio');
        $audioPath = $this->voiceNoteService->storeUploadedFile($file, $user->id);

        $this->voiceNoteService->broadcastToChannel($channel, $user->id, $audioPath);

        return response()->json([
            'success' => true,
            'message' => 'Broadcast berhasil.',
            'data' => [
                'channel_id' => $channelId,
                'broadcast_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function getUserChannels(Request $request)
    {
        $user = $request->user();
        $channels = $this->voiceNoteService->getUserChannels($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Daftar channel PTT.',
            'data' => $channels,
        ]);
    }

    public function leaveChannel(Request $request, int $channelId)
    {
        $user = $request->user();

        \App\Models\VoiceChannelMember::where('voice_channel_id', $channelId)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil meninggalkan channel.',
        ]);
    }
}

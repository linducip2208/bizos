<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineAction extends Model
{
    protected $fillable = [
        'user_id',
        'action_type',
        'action_data',
        'status',
        'server_response',
        'attempted_at',
        'synced_at',
        'error_message',
    ];

    protected $casts = [
        'action_data' => 'array',
        'server_response' => 'array',
        'attempted_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeConflict($query)
    {
        return $query->where('status', 'conflict');
    }

    public function markSynced(array $response = []): void
    {
        $this->update([
            'status' => 'synced',
            'server_response' => $response,
            'synced_at' => now(),
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'attempted_at' => now(),
        ]);
    }

    public function markConflict(): void
    {
        $this->update([
            'status' => 'conflict',
            'attempted_at' => now(),
        ]);
    }
}

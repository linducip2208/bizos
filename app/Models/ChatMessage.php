<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    const UPDATED_AT = null;

    protected $table = 'chat_messages';

    protected $fillable = [
        'chat_id',
        'sender_id',
        'message_type',
        'message',
        'file_path',
        'file_name',
        'file_size',
        'reply_to_id',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(Employee::class, 'sender_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(ChatMessage::class, 'reply_to_id');
    }

    public function reads()
    {
        return $this->hasMany(ChatMessageRead::class, 'message_id');
    }

    public function reactions()
    {
        return $this->hasMany(ChatReaction::class, 'message_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessageRead extends Model
{
    const UPDATED_AT = null;

    protected $table = 'chat_message_reads';

    protected $fillable = [
        'message_id',
        'employee_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

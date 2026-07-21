<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatReaction extends Model
{
    const UPDATED_AT = null;

    protected $table = 'chat_reactions';

    protected $fillable = [
        'message_id',
        'employee_id',
        'reaction',
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

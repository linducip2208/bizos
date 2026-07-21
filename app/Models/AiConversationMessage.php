<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiConversationMessage extends Model
{
    public $timestamps = true;

    const UPDATED_AT = null;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tokens_used',
        'created_at',
    ];

    protected $casts = [
        'tokens_used' => 'integer',
        'created_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}

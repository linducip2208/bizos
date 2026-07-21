<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiConversation extends Model
{
    protected $fillable = [
        'employee_id',
        'provider_id',
        'title',
        'model',
        'context_type',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function provider()
    {
        return $this->belongsTo(AiProvider::class, 'provider_id');
    }

    public function messages()
    {
        return $this->hasMany(AiConversationMessage::class, 'conversation_id');
    }
}

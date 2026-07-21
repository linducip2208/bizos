<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatParticipant extends Model
{
    protected $table = 'chat_participants';

    protected $fillable = [
        'chat_id',
        'employee_id',
        'role',
        'last_read_at',
        'joined_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'joined_at' => 'datetime',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeerRecognition extends Model
{
    protected $fillable = [
        'company_id',
        'from_user_id',
        'to_user_id',
        'badge',
        'message',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientSegmentMember extends Model
{
    protected $table = 'client_segment_members';

    protected $fillable = [
        'segment_id',
        'client_id',
        'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function segment()
    {
        return $this->belongsTo(ClientSegment::class, 'segment_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

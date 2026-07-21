<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    protected $fillable = [
        'reply_id',
        'ticket_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function reply()
    {
        return $this->belongsTo(TicketReply::class, 'reply_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(Employee::class, 'uploaded_by');
    }
}

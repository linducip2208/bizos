<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAttachment extends Model
{
    protected $fillable = [
        'email_message_id',
        'filename',
        'mime_type',
        'size_bytes',
        'storage_path',
        'content_id',
        'is_inline',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'is_inline' => 'boolean',
    ];

    public function message()
    {
        return $this->belongsTo(EmailMessage::class, 'email_message_id');
    }
}

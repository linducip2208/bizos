<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email_account_id',
        'message_uid',
        'message_id',
        'from_email',
        'from_name',
        'to_email',
        'cc',
        'bcc',
        'subject',
        'body_html',
        'body_text',
        'folder',
        'is_read',
        'is_starred',
        'is_draft',
        'is_sent',
        'has_attachments',
        'email_date',
        'in_reply_to',
        'headers',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'is_draft' => 'boolean',
        'is_sent' => 'boolean',
        'has_attachments' => 'boolean',
        'email_date' => 'datetime',
        'headers' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function attachments()
    {
        return $this->hasMany(EmailAttachment::class);
    }

    public function links()
    {
        return $this->hasMany(EmailLink::class, 'message_id', 'message_id');
    }
}

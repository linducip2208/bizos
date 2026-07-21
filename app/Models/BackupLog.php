<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    protected $fillable = ['filename', 'file_size', 'type', 'status', 'error_message', 'storage_path', 'schedule_name'];

    public $timestamps = false;

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];
}

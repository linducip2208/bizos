<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    protected $fillable = ['filename', 'file_size', 'type', 'status', 'error_message'];

    public $timestamps = false;

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppFile extends Model
{
    use SoftDeletes;

    protected $table = 'files';

    protected $fillable = [
        'folder_id',
        'uploaded_by',
        'file_name',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
        'extension',
        'is_public',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_public' => 'boolean',
    ];

    public function folder()
    {
        return $this->belongsTo(FileFolder::class, 'folder_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(Employee::class, 'uploaded_by');
    }
}

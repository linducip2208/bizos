<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileFolder extends Model
{
    protected $table = 'file_folders';

    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function parent()
    {
        return $this->belongsTo(FileFolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(FileFolder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(AppFile::class, 'folder_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    protected $fillable = [
        'company_id',
        'project_id',
        'requested_by',
        'required_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'required_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    public function items()
    {
        return $this->hasMany(MaterialRequestItem::class);
    }
}

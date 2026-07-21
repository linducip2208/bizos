<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessUnit extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'parent_id',
        'manager_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function parent()
    {
        return $this->belongsTo(BusinessUnit::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BusinessUnit::class, 'parent_id');
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}

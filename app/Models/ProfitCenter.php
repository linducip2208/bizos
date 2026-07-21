<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitCenter extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'department_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function coaAccounts()
    {
        return $this->hasMany(Coa::class);
    }
}

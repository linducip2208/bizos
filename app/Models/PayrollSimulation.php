<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSimulation extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'config',
        'result',
        'created_by',
    ];

    protected $casts = [
        'config' => 'array',
        'result' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}

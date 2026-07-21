<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Okr extends Model
{
    protected $fillable = [
        'company_id',
        'employee_id',
        'department_id',
        'title',
        'description',
        'year',
        'quarter',
        'type',
        'progress_percent',
    ];

    protected $casts = [
        'year' => 'integer',
        'quarter' => 'integer',
        'type' => 'string',
        'progress_percent' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function keyResults()
    {
        return $this->hasMany(OkrKeyResult::class);
    }
}

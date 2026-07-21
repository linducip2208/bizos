<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    protected $fillable = [
        'employee_id',
        'name',
        'relationship',
        'gender',
        'birth_date',
        'occupation',
        'phone',
        'is_emergency_contact',
        'is_dependent',
        'nik',
        'kk_number',
    ];

    protected $casts = [
        'relationship' => 'string',
        'gender' => 'string',
        'birth_date' => 'date',
        'is_emergency_contact' => 'boolean',
        'is_dependent' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

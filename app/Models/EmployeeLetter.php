<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLetter extends Model
{
    protected $fillable = [
        'company_id',
        'employee_id',
        'letter_type',
        'letter_number',
        'subject',
        'content',
        'status',
        'issued_by',
        'issued_at',
    ];

    protected $casts = [
        'letter_type' => 'string',
        'status' => 'string',
        'issued_at' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function issuer()
    {
        return $this->belongsTo(Employee::class, 'issued_by');
    }
}

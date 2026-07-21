<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'status',
        'collect_email',
        'max_submissions',
        'current_submissions',
        'expiration_date',
        'created_by',
    ];

    protected $casts = [
        'collect_email' => 'boolean',
        'max_submissions' => 'integer',
        'current_submissions' => 'integer',
        'expiration_date' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function fields()
    {
        return $this->hasMany(FormField::class);
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    public $timestamps = false;

    const UPDATED_AT = null;

    protected $fillable = [
        'form_id',
        'submitter_email',
        'submitted_by',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(Employee::class, 'submitted_by');
    }

    public function values()
    {
        return $this->hasMany(FormFieldValue::class, 'submission_id');
    }
}

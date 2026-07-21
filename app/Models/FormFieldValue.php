<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormFieldValue extends Model
{
    const UPDATED_AT = null;

    protected $table = 'form_field_values';

    protected $fillable = [
        'submission_id',
        'field_id',
        'value',
    ];

    public function submission()
    {
        return $this->belongsTo(FormSubmission::class);
    }

    public function field()
    {
        return $this->belongsTo(FormField::class);
    }
}

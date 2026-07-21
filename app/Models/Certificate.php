<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = ['enrollment_id', 'employee_id', 'certificate_number', 'issued_date', 'uuid', 'pdf_path'];
    protected $casts = ['issued_date' => 'date'];

    public function enrollment() { return $this->belongsTo(CourseEnrollment::class); }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

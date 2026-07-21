<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementAttachment extends Model
{
    protected $fillable = [
        'reimbursement_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function reimbursement()
    {
        return $this->belongsTo(Reimbursement::class);
    }
}

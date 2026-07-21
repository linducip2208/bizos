<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IsoPolicy extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'policy_number',
        'category',
        'description',
        'version',
        'content',
        'document_path',
        'effective_date',
        'review_due',
        'approved_by',
        'status',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'review_due' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function acknowledgments()
    {
        return $this->hasMany(IsoPolicyAcknowledgment::class);
    }
}

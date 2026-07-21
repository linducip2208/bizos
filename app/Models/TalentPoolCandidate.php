<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TalentPoolCandidate extends Model
{
    protected $fillable = [
        'talent_pool_id',
        'candidate_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function talentPool()
    {
        return $this->belongsTo(TalentPool::class);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}

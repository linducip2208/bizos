<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NlQueryLog extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'question',
        'intent',
        'classification',
        'answer_text',
        'execution_time_ms',
    ];

    protected $casts = [
        'execution_time_ms' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpeechTranscript extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'audio_path',
        'transcript',
        'language',
        'duration',
        'confidence',
    ];

    protected $casts = [
        'duration' => 'decimal:2',
        'confidence' => 'decimal:4',
    ];

    public $timestamps = false;

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

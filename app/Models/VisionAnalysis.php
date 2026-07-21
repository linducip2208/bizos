<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisionAnalysis extends Model
{
    protected $fillable = [
        'company_id',
        'image_path',
        'prompt',
        'result',
        'model_used',
    ];

    public $timestamps = false;

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

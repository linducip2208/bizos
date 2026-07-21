<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionCompetency extends Model
{
    protected $fillable = ['position_id', 'competency_id', 'required_level', 'weight'];

    protected $casts = [
        'required_level' => 'integer',
        'weight' => 'decimal:2',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function competency()
    {
        return $this->belongsTo(Competency::class);
    }
}

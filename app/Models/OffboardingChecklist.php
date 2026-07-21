<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffboardingChecklist extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(OffboardingChecklistItem::class, 'checklist_id')->orderBy('sort_order');
    }

    public function progress()
    {
        return $this->hasMany(OffboardingProgress::class, 'checklist_id');
    }
}

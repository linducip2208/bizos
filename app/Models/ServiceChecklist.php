<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceChecklist extends Model
{
    protected $table = 'service_checklists';

    protected $fillable = [
        'company_id',
        'service_type',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(ServiceChecklistItem::class, 'checklist_id')->orderBy('sort_order');
    }
}

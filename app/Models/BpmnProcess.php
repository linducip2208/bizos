<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpmnProcess extends Model
{
    protected $table = 'bpmn_processes';

    protected $fillable = [
        'company_id', 'name', 'category', 'description', 'bpmn_xml',
        'diagram_svg', 'is_prebuilt', 'is_active', 'version', 'sla_hours',
    ];

    protected $casts = [
        'is_prebuilt' => 'boolean',
        'is_active' => 'boolean',
        'version' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function instances()
    {
        return $this->hasMany(BpmnProcessInstance::class, 'process_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiKnowledgeBase extends Model
{
    protected $table = 'ai_knowledge_base';

    protected $fillable = [
        'company_id',
        'title',
        'content',
        'source_type',
        'source_path',
        'chunks_json',
        'embedding_vector',
        'is_active',
    ];

    protected $casts = [
        'embedding_vector' => 'array',
        'chunks_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

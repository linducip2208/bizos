<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'content',
        'category',
        'language',
        'status',
        'meta_template_id',
        'meta_template_status',
        'meta_rejection_reason',
        'components',
        'quality_score',
        'meta_synced_at',
        'rejected_at',
    ];

    protected $casts = [
        'language' => 'string',
        'status' => 'string',
        'components' => 'array',
        'meta_synced_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function waBlastCampaigns()
    {
        return $this->hasMany(WaBlastCampaign::class, 'template_id');
    }

    public function isSyncedToMeta(): bool
    {
        return !empty($this->meta_template_id);
    }

    public function isApproved(): bool
    {
        return $this->meta_template_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->meta_template_status === 'rejected';
    }

    public function isPendingApproval(): bool
    {
        return $this->meta_template_status === 'pending_approval';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->meta_template_status ?: $this->status) {
            'draft' => 'Draft',
            'pending_approval' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'paused' => 'Dinonaktifkan',
            'aktif' => 'Aktif',
            'ditolak' => 'Ditolak',
            default => 'Draft',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->meta_template_status ?: $this->status) {
            'draft' => 'gray',
            'pending_approval' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'paused' => 'gray',
            'aktif' => 'success',
            'ditolak' => 'danger',
            default => 'gray',
        };
    }
}

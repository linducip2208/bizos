<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'logo',
        'address',
        'phone',
        'email',
        'website',
        'tax_id',
        'is_active',
        'is_suspended',
        'suspended_reason',
        'suspended_at',
        'data_retention_days',
        'subscription_start',
        'subscription_end',
        'is_sandbox',
        'sandbox_source_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_suspended' => 'boolean',
        'is_sandbox' => 'boolean',
        'suspended_at' => 'datetime',
        'data_retention_days' => 'integer',
        'subscription_start' => 'date',
        'subscription_end' => 'date',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function designations()
    {
        return $this->hasMany(Designation::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function sandboxSource()
    {
        return $this->belongsTo(Company::class, 'sandbox_source_id');
    }

    public function sandboxes()
    {
        return $this->hasMany(Company::class, 'sandbox_source_id');
    }

    public function waTemplates()
    {
        return $this->hasMany(WaTemplate::class);
    }

    public function waConversations()
    {
        return $this->hasMany(WaConversation::class);
    }

    public function waAutoReplies()
    {
        return $this->hasMany(WaAutoReply::class);
    }

    public function waBlastCampaigns()
    {
        return $this->hasMany(WaBlastCampaign::class);
    }

    public function chatbotFlows()
    {
        return $this->hasMany(ChatbotFlow::class);
    }

    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }

    public function usageLogs()
    {
        return $this->hasMany(TenantUsageLog::class);
    }

    public function wasteRecords()
    {
        return $this->hasMany(WasteRecord::class);
    }

    public function waterUsages()
    {
        return $this->hasMany(WaterUsage::class);
    }

    public function esgTargets()
    {
        return $this->hasMany(EsgTarget::class);
    }

    public function esgReports()
    {
        return $this->hasMany(EsgReport::class);
    }

    public function carbonCalculations()
    {
        return $this->hasMany(CarbonCalculation::class);
    }

    public function integrationConnectors()
    {
        return $this->hasMany(IntegrationConnector::class);
    }

    public function virtualAccounts()
    {
        return $this->hasMany(VirtualAccount::class);
    }

    public function djpTokens()
    {
        return $this->hasMany(DjpToken::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function energyMeters()
    {
        return $this->hasMany(EnergyMeter::class);
    }

    public function energyReadings()
    {
        return $this->hasMany(EnergyReading::class);
    }

    public function isSuspended(): bool
    {
        return $this->is_suspended;
    }

    public function isActive(): bool
    {
        return $this->is_active && !$this->is_suspended;
    }
}

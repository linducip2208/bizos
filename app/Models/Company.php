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

    public function aiAgents()
    {
        return $this->hasMany(AiAgent::class);
    }

    public function aiWorkflows()
    {
        return $this->hasMany(AiWorkflow::class);
    }

    public function aiPrompts()
    {
        return $this->hasMany(AiPrompt::class);
    }

    public function speechTranscripts()
    {
        return $this->hasMany(SpeechTranscript::class);
    }

    public function visionAnalyses()
    {
        return $this->hasMany(VisionAnalysis::class);
    }

    public function kpiDefinitions()
    {
        return $this->hasMany(KpiDefinition::class);
    }

    public function oauthProviders()
    {
        return $this->hasMany(OauthProvider::class);
    }

    public function ssoConfigs()
    {
        return $this->hasMany(SsoConfig::class);
    }

    public function shippingProviders()
    {
        return $this->hasMany(ShippingProvider::class);
    }

    public function erpConnectors()
    {
        return $this->hasMany(ErpConnector::class);
    }

    public function plugins()
    {
        return $this->hasMany(Plugin::class);
    }

    public function featureFlags()
    {
        return $this->hasMany(FeatureFlag::class);
    }

    public function jobMonitors()
    {
        return $this->hasMany(JobMonitor::class);
    }

    public function queueMonitors()
    {
        return $this->hasMany(QueueMonitor::class);
    }

    public function systemHealthChecks()
    {
        return $this->hasMany(SystemHealthCheck::class);
    }

    public function systemLogs()
    {
        return $this->hasMany(SystemLog::class);
    }

    public function businessUnits()
    {
        return $this->hasMany(BusinessUnit::class);
    }

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    public function employmentTypes()
    {
        return $this->hasMany(EmploymentType::class);
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }

    public function workCalendars()
    {
        return $this->hasMany(WorkCalendar::class);
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function activityTimeline()
    {
        return $this->hasMany(ActivityTimeline::class);
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

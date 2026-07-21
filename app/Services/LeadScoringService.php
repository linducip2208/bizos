<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadActivityLog;
use App\Models\LeadScore;
use Carbon\Carbon;

class LeadScoringService
{
    public function calculateScore(Lead $lead): int
    {
        $criteria = [];
        $score = 0;

        $companyFieldCount = 0;
        if (!empty($lead->company_name)) $companyFieldCount++;
        if (!empty($lead->industry)) $companyFieldCount++;
        if (!empty($lead->phone)) $companyFieldCount++;
        if (!empty($lead->address)) $companyFieldCount++;
        $completenessScore = min($companyFieldCount * 5, 15);
        $criteria['profile_completeness'] = $completenessScore;
        $score += $completenessScore;

        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $activityCount = LeadActivityLog::where('lead_id', $lead->id)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $activityScore = min($activityCount * 3, 20);
        $criteria['recent_activity'] = $activityScore;
        $score += $activityScore;

        $emailOpens = LeadActivityLog::where('lead_id', $lead->id)
            ->where('activity_type', 'email_opened')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $emailClicks = LeadActivityLog::where('lead_id', $lead->id)
            ->where('activity_type', 'email_clicked')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $engagementScore = min(($emailOpens * 2) + ($emailClicks * 5), 20);
        $criteria['email_engagement'] = $engagementScore;
        $score += $engagementScore;

        $pageVisits = LeadActivityLog::where('lead_id', $lead->id)
            ->where('activity_type', 'page_visited')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $formSubmissions = LeadActivityLog::where('lead_id', $lead->id)
            ->where('activity_type', 'form_submitted')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $webScore = min(($pageVisits * 2) + ($formSubmissions * 10), 20);
        $criteria['web_interaction'] = $webScore;
        $score += $webScore;

        $dealCount = Deal::where('lead_id', $lead->id)->count();
        $dealScore = $dealCount > 0 ? min($dealCount * 10, 15) : 0;
        $criteria['deals'] = $dealScore;
        $score += $dealScore;

        $totalActivities = LeadActivity::where('lead_id', $lead->id)->count();
        $salesEngagement = min($totalActivities * 2, 10);
        $criteria['sales_engagement'] = $salesEngagement;
        $score += $salesEngagement;

        LeadScore::create([
            'company_id' => $lead->company_id,
            'lead_id' => $lead->id,
            'score' => $score,
            'criteria' => $criteria,
            'calculated_at' => now(),
        ]);

        $lead->update(['score' => $score]);

        return $score;
    }

    public function recalculateAll(): int
    {
        $count = 0;
        $leads = Lead::all();

        foreach ($leads as $lead) {
            $this->calculateScore($lead);
            $count++;
        }

        return $count;
    }

    public function getLeadGrade(Lead $lead): string
    {
        $score = $lead->score ?? 0;

        if ($score >= 70) {
            return 'hot';
        }

        if ($score >= 40) {
            return 'warm';
        }

        return 'cold';
    }
}

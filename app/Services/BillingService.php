<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionUsage;
use Carbon\Carbon;

class BillingService
{
    public function subscribe(Company $company, SubscriptionPlan $plan, int $trialDays = 14): Subscription
    {
        $existing = Subscription::where('company_id', $company->id)
            ->whereIn('status', ['trial', 'active', 'grace'])
            ->first();

        if ($existing) {
            $existing->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }

        $startedAt = now();
        $trialEndsAt = $startedAt->copy()->addDays($trialDays);

        return Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'started_at' => $startedAt,
            'ends_at' => null,
            'trial_ends_at' => $trialEndsAt,
            'status' => 'trial',
            'auto_renew' => true,
        ]);
    }

    public function cancel(Subscription $subscription): void
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew' => false,
        ]);
    }

    public function renew(Subscription $subscription): void
    {
        $plan = $subscription->plan;
        $now = now();

        $startDate = $subscription->ends_at && $subscription->ends_at->isFuture()
            ? $subscription->ends_at
            : $now;

        $subscription->update([
            'status' => 'active',
            'started_at' => $subscription->status === 'trial' ? $now : $subscription->started_at,
            'ends_at' => $startDate->copy()->addMonth(),
            'trial_ends_at' => $subscription->status === 'trial' ? null : $subscription->trial_ends_at,
        ]);

        $this->generateInvoice($subscription);
    }

    public function generateInvoice(Subscription $subscription): SubscriptionInvoice
    {
        $company = $subscription->company;
        $plan = $subscription->plan;
        $now = now();

        $periodStart = $subscription->ends_at && $subscription->ends_at->isFuture()
            ? $subscription->ends_at->copy()
            : $now->copy();

        $periodEnd = $periodStart->copy()->addMonth();

        $amount = (float) $plan->monthly_price;
        $taxAmount = round($amount * 0.11, 2);
        $total = round($amount + $taxAmount, 2);

        $invoiceNumber = $this->generateInvoiceNumber($company);

        return SubscriptionInvoice::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'status' => 'pending',
            'due_date' => $now->copy()->addDays(7)->toDateString(),
        ]);
    }

    public function checkUsage(Company $company, string $metric): bool
    {
        $subscription = $this->getActiveSubscription($company);

        if (!$subscription) {
            return false;
        }

        $plan = $subscription->plan;

        $limit = match ($metric) {
            'users' => $plan->max_users ?? 0,
            'companies' => $plan->max_companies ?? 1,
            'branches' => $plan->max_branches ?? 1,
            'projects' => PHP_INT_MAX,
            'storage_mb' => PHP_INT_MAX,
            'transactions' => PHP_INT_MAX,
            default => PHP_INT_MAX,
        };

        $currentUsage = $this->getCurrentUsageCount($company, $subscription, $metric);

        return $currentUsage < $limit;
    }

    public function recordUsage(Company $company, string $metric, float $count): void
    {
        $subscription = $this->getActiveSubscription($company);

        if (!$subscription) {
            return;
        }

        SubscriptionUsage::updateOrCreate(
            [
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'metric' => $metric,
                'recorded_at' => now()->toDateString(),
            ],
            [
                'usage_count' => $count,
            ]
        );
    }

    public function getFeatureAccess(Company $company): array
    {
        $subscription = $this->getActiveSubscription($company);

        if (!$subscription) {
            return [];
        }

        $plan = $subscription->plan;

        $baseFeatures = $plan->getFeatureList();

        $tierFeatures = match ($plan->tier) {
            'platinum' => [
                'all_features',
                'priority_support',
                'custom_domain',
                'white_label',
                'api_access',
                'advanced_reporting',
                'bulk_operations',
                'audit_export',
                'custom_workflows',
                'role_based_dashboard',
            ],
            'gold' => [
                'advanced_reporting',
                'bulk_operations',
                'audit_export',
                'api_access',
            ],
            'standard' => [
                'basic_reporting',
            ],
            default => [],
        };

        return array_values(array_unique(array_merge($baseFeatures, $tierFeatures)));
    }

    public function getActiveSubscription(Company $company): ?Subscription
    {
        return Subscription::with('plan')
            ->where('company_id', $company->id)
            ->whereIn('status', ['trial', 'active', 'grace'])
            ->latest()
            ->first();
    }

    public function checkAndExpireSubscriptions(): int
    {
        $count = 0;

        $subscriptions = Subscription::with('company', 'plan')
            ->whereIn('status', ['trial', 'active', 'grace'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            if ($subscription->status === 'grace') {
                $subscription->update(['status' => 'expired']);
                $count++;
                continue;
            }

            if ($subscription->auto_renew && $subscription->status !== 'trial') {
                if ($subscription->status === 'active') {
                    $subscription->update(['status' => 'grace']);
                    $count++;
                }
            } else {
                $subscription->update(['status' => 'expired']);
                $count++;
            }
        }

        $trials = Subscription::with('company', 'plan')
            ->where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($trials as $subscription) {
            $subscription->update(['status' => 'expired']);
            $count++;
        }

        return $count;
    }

    public function generateDueInvoices(): int
    {
        $count = 0;

        $subscriptions = Subscription::with('company', 'plan')
            ->whereIn('status', ['active', 'grace'])
            ->where('auto_renew', true)
            ->get();

        foreach ($subscriptions as $subscription) {
            if (!$subscription->ends_at) {
                continue;
            }

            $daysUntilEnd = (int) now()->diffInDays($subscription->ends_at, false);

            if ($daysUntilEnd <= 7 && $daysUntilEnd >= 0) {
                $existingInvoice = SubscriptionInvoice::where('subscription_id', $subscription->id)
                    ->where('period_end', '>=', now()->toDateString())
                    ->where('period_start', '<=', now()->toDateString())
                    ->exists();

                if (!$existingInvoice) {
                    $this->generateInvoice($subscription);
                    $count++;
                }
            }
        }

        return $count;
    }

    public function checkOverdueInvoices(): int
    {
        $count = 0;

        $invoices = SubscriptionInvoice::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->update(['status' => 'overdue']);
            $count++;
        }

        return $count;
    }

    protected function generateInvoiceNumber(Company $company): string
    {
        $prefix = 'INV-SUB-' . strtoupper(substr($company->code ?? 'XX', 0, 4));
        $date = now()->format('Ymd');
        $count = SubscriptionInvoice::where('company_id', $company->id)
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return $prefix . '-' . $date . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    protected function getCurrentUsageCount(Company $company, Subscription $subscription, string $metric): float
    {
        return match ($metric) {
            'users' => (float) $company->users()->count(),
            'companies' => 1.0,
            'branches' => (float) $company->branches()->count(),
            'projects' => (float) $company->allProjectsCount() ?? 0,
            'storage_mb' => 0.0,
            'transactions' => 0.0,
            default => (float) SubscriptionUsage::where('company_id', $company->id)
                ->where('subscription_id', $subscription->id)
                ->where('metric', $metric)
                ->where('recorded_at', now()->toDateString())
                ->value('usage_count') ?? 0.0,
        };
    }
}

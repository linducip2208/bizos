<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update permission slugs to match renamed navigation groups.
     *
     * The HasPermissionAccess trait derives permission groups from
     * getNavigationGroup() via: strtolower(str_replace(' ', '-', $group))
     *
     * When navigation groups were renamed, permission slugs drifted.
     * This migration realigns them.
     *
     * OLD → NEW mapping:
     *   "🏠 Dashboard & Reporting"  → "🏠 Dashboard"
     *   "🤖 AI Assistant"           → "🤖 AI"
     *   "⚡ Automation & Workflow"  → "⚡ Automation"
     *   "🌱 ESG & Sustainability"   → "🌱 ESG"
     *   "⚙️ Sistem"                 → "⚙️ System"
     *   "📦 Product & Inventory"    → "📑 Procurement" + "📦 Inventory" (split)
     *   "📈 Sales & CRM"            → "📈 Sales" + "📢 Marketing" (split)
     */
    public function up(): void
    {
        // ─── 1. Unambiguous renames (simple prefix replacement) ──────

        $this->replacePrefix('🏠-dashboard-&-reporting', '🏠-dashboard');
        $this->replacePrefix('🤖-ai-assistant', '🤖-ai');
        $this->replacePrefix('⚡-automation-&-workflow', '⚡-automation');
        $this->replacePrefix('🌱-esg-&-sustainability', '🌱-esg');
        $this->replacePrefix('⚙️-sistem', '⚙️-system');

        // ─── 2. "📦 Product & Inventory" → "📑 Procurement" ───
        // Resources that moved to Procurement:
        //   PurchaseRequisition, PurchaseOrder, GoodsReceipt, Supplier,
        //   PurchaseRequisitionItem, PurchaseOrderItem, GoodsReceiptItem
        // (Specific slug suffixes below — update if your project differs)
        $procurementSuffixes = [
            'purchase-requisition',
            'purchase-order',
            'goods-receipt',
            'supplier',
            'purchase-requisition-item',
            'purchase-order-item',
            'goods-receipt-item',
        ];

        foreach ($procurementSuffixes as $suffix) {
            $old = "📦-product-&-inventory.{$suffix}";
            $new = "📑-procurement.{$suffix}";
            DB::statement("UPDATE permissions SET slug = ? WHERE slug LIKE ?", [
                $new, "📦-product-&-inventory.{$suffix}%"
            ]);
        }

        // ─── 3. "📦 Product & Inventory" → "📦 Inventory" ───
        // Everything else that was in Product & Inventory stays in Inventory
        $this->replacePrefix('📦-product-&-inventory', '📦-inventory');

        // ─── 4. "📈 Sales & CRM" → "📈 Sales" ───
        // Resources that moved to Sales:
        //   Client, Deal, Lead, Quotation, SalesOrder, SalesInvoice,
        //   SalesReturn, LeadSource, PipelineStage, Referral,
        //   ClientContact, ClientSegment, PriceList, Coupon,
        //   SalesTarget, CommissionSlab, Promotion, EmailLog, CallLog
        $salesSuffixes = [
            'client',
            'deal',
            'lead',
            'quotation',
            'sales-order',
            'sales-invoice',
            'sales-return',
            'lead-source',
            'pipeline-stage',
            'referral',
            'client-contact',
            'client-segment',
            'price-list',
            'coupon',
            'sales-target',
            'commission-slab',
            'promotion',
            'email-log',
            'call-log',
        ];

        foreach ($salesSuffixes as $suffix) {
            $old = "📈-sales-&-crm.{$suffix}";
            $new = "📈-sales.{$suffix}";
            DB::statement("UPDATE permissions SET slug = ? WHERE slug LIKE ?", [
                $new, "📈-sales-&-crm.{$suffix}%"
            ]);
        }

        // ─── 5. "📈 Sales & CRM" → "📢 Marketing" ───
        $marketingSuffixes = [
            'blog-post',
            'blog-category',
            'campaign',
            'landing-page',
            'email-campaign',
            'marketing-automation',
            'lead-score',
            'wa-template',
            'wa-auto-reply',
            'wa-blast-campaign',
            'wa-blast-log',
            'wa-conversation',
            'chatbot-flow',
        ];

        foreach ($marketingSuffixes as $suffix) {
            $old = "📈-sales-&-crm.{$suffix}";
            $new = "📢-marketing.{$suffix}";
            DB::statement("UPDATE permissions SET slug = ? WHERE slug LIKE ?", [
                $new, "📈-sales-&-crm.{$suffix}%"
            ]);
        }

        // Clean up any remaining old Sales & CRM slugs (catch-all → Sales)
        $this->replacePrefix('📈-sales-&-crm', '📈-sales');

        // ─── 6. Fleet resources from Organisasi → 🚛 Fleet Management ───
        $fleetSuffixes = ['vehicle', 'vehicle-fuel-log', 'vehicle-maintenance-log', 'vehicle-assignment'];
        foreach ($fleetSuffixes as $suffix) {
            DB::statement("UPDATE permissions SET slug = ? WHERE slug LIKE ?", [
                "🚛-fleet-management.{$suffix}", "🏢-organisasi.{$suffix}%"
            ]);
        }

        // ─── 7. Dashboard resources that moved to System ───
        DB::statement("UPDATE permissions SET slug = ? WHERE slug LIKE ?", [
            '⚙️-system.activity-timeline', '🏠-dashboard-&-reporting.activity-timeline%'
        ]);

        // ─── 8. Dashboard resources that moved to Reports & Analytics ───
        $reportSuffixes = ['report-template', 'report-schedule', 'advanced-report'];
        foreach ($reportSuffixes as $suffix) {
            DB::statement("UPDATE permissions SET slug = ? WHERE slug LIKE ?", [
                "📊-reports-&-analytics.{$suffix}", "🏠-dashboard-&-reporting.{$suffix}%"
            ]);
        }

        // ─── 9. Catch-all: remaining 🏠 Dashboard & Reporting → 🏠 Dashboard ───
        $this->replacePrefix('🏠-dashboard-&-reporting', '🏠-dashboard');
    }

    public function down(): void
    {
        // Reverse unambiguous renames
        $this->replacePrefix('🏠-dashboard', '🏠-dashboard-&-reporting');
        $this->replacePrefix('🤖-ai', '🤖-ai-assistant');
        $this->replacePrefix('⚡-automation', '⚡-automation-&-workflow');
        $this->replacePrefix('🌱-esg', '🌱-esg-&-sustainability');
        $this->replacePrefix('⚙️-system', '⚙️-sistem');

        // Reverse Procurement → Product & Inventory
        $this->replacePrefix('📑-procurement', '📦-product-&-inventory');

        // Reverse Inventory → Product & Inventory
        $this->replacePrefix('📦-inventory', '📦-product-&-inventory');

        // Reverse Sales → Sales & CRM
        $this->replacePrefix('📈-sales', '📈-sales-&-crm');

        // Reverse Marketing → Sales & CRM
        $this->replacePrefix('📢-marketing', '📈-sales-&-crm');
    }

    private function replacePrefix(string $oldPrefix, string $newPrefix): void
    {
        DB::statement('UPDATE permissions SET slug = REPLACE(slug, ?, ?) WHERE slug LIKE ?', [
            $oldPrefix,
            $newPrefix,
            $oldPrefix . '.%',
        ]);
    }
};

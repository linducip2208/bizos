<?php
/**
 * BizOS Navigation Group Restructure
 * Maps all 289+ resources into 23 clean navigation groups
 * Run: php scripts/restructure_nav_groups.php
 */

$basePath = __DIR__ . '/..';

// ─── COMPLETE MAPPING: directory-name => new-group ──────────────────────────
$mapping = [
    // 🏠 Dashboard & Reporting
    'ActivityTimeline'       => '🏠 Dashboard & Reporting',
    'AdvancedReports'        => '🏠 Dashboard & Reporting',
    'DashboardLayouts'       => '🏠 Dashboard & Reporting',
    'ReportSchedules'        => '🏠 Dashboard & Reporting',
    'ReportTemplates'        => '🏠 Dashboard & Reporting',

    // 🏢 Organisasi
    'Branches'               => '🏢 Organisasi',
    'BusinessUnits'          => '🏢 Organisasi',
    'Companies'              => '🏢 Organisasi',
    'Departments'            => '🏢 Organisasi',
    'Designations'           => '🏢 Organisasi',
    'Divisions'              => '🏢 Organisasi',
    'Employees'              => '🏢 Organisasi',
    'EmploymentTypes'        => '🏢 Organisasi',
    'Grades'                 => '🏢 Organisasi',
    'Holidays'               => '🏢 Organisasi',
    'Positions'              => '🏢 Organisasi',
    'Sections'               => '🏢 Organisasi',
    'Vehicle'                => '🏢 Organisasi',
    'VehicleAssignment'      => '🏢 Organisasi',
    'VehicleFuelLog'         => '🏢 Organisasi',
    'VehicleMaintenanceLog'  => '🏢 Organisasi',
    'WorkCalendars'          => '🏢 Organisasi',

    // 👥 Human Capital
    'Allowance'              => '💰 Payroll',
    'Announcements'          => '👥 Human Capital',
    'AttendanceConfigs'      => '👥 Human Capital',
    'Attendances'            => '👥 Human Capital',
    'Bonus'                  => '💰 Payroll',
    'Candidates'             => '👥 Human Capital',
    'CanteenMenus'           => '👥 Human Capital',
    'CanteenOrderItems'      => '👥 Human Capital',
    'CanteenOrders'          => '👥 Human Capital',
    'Competencies'           => '👥 Human Capital',
    'Deduction'              => '💰 Payroll',
    'FeedbackAnswers'        => '👥 Human Capital',
    'FeedbackCycles'         => '👥 Human Capital',
    'FeedbackQuestions'      => '👥 Human Capital',
    'FeedbackReviewers'      => '👥 Human Capital',
    'Interviewers'           => '👥 Human Capital',
    'InterviewResults'       => '👥 Human Capital',
    'Interviews'             => '👥 Human Capital',
    'JobPostings'            => '👥 Human Capital',
    'KpiIndicators'          => '👥 Human Capital',
    'KpiTemplates'           => '👥 Human Capital',
    'Leaves'                 => '👥 Human Capital',
    'LeaveTypes'             => '👥 Human Capital',
    'OffboardingChecklists'  => '👥 Human Capital',
    'OnboardingChecklists'   => '👥 Human Capital',
    'Overtimes'              => '👥 Human Capital',
    'PerformanceCycles'      => '👥 Human Capital',
    'PerformanceReviews'     => '👥 Human Capital',
    'ReimbursementCategories'=> '👥 Human Capital',
    'Reimbursements'         => '👥 Human Capital',
    'Shifts'                 => '👥 Human Capital',
    'SuccessionPlans'        => '👥 Human Capital',
    'Visits'                 => '👥 Human Capital',
    'WifiAccessPoints'       => '👥 Human Capital',

    // 💰 Payroll
    'BpjsConfig'             => '💰 Payroll',
    'EmployeeSalaryComponent'=> '💰 Payroll',
    'Payroll'                => '💰 Payroll',
    'PayrollApproval'        => '💰 Payroll',
    'PayrollItem'            => '💰 Payroll',
    'PayrollPeriod'          => '💰 Payroll',
    'PayrollSimulation'      => '💰 Payroll',
    'PaySlip'                => '💰 Payroll',
    'Pph21Config'            => '💰 Payroll',
    'SalaryComponent'        => '💰 Payroll',
    'ThrConfig'              => '💰 Payroll',

    // 💵 Finance & Accounting
    'AssetCategory'          => '💵 Finance & Accounting',
    'AssetDepreciation'      => '💵 Finance & Accounting',
    'AssetMaintenance'       => '💵 Finance & Accounting',
    'AssetMutation'          => '💵 Finance & Accounting',
    'Assets'                 => '💵 Finance & Accounting',
    'BankAccount'            => '💵 Finance & Accounting',
    'BankFacility'           => '💵 Finance & Accounting',
    'BankReconciliation'     => '💵 Finance & Accounting',
    'BankTransaction'        => '💵 Finance & Accounting',
    'BankTransfer'           => '💵 Finance & Accounting',
    'Budget'                 => '💵 Finance & Accounting',
    'BudgetItem'             => '💵 Finance & Accounting',
    'Coa'                    => '💵 Finance & Accounting',
    'CoaBalance'             => '💵 Finance & Accounting',
    'CoaCategory'            => '💵 Finance & Accounting',
    'Currency'               => '💵 Finance & Accounting',
    'ExchangeRateLog'        => '💵 Finance & Accounting',
    'Investments'            => '💵 Finance & Accounting',
    'InvoiceItem'            => '💵 Finance & Accounting',
    'InvoicePayment'         => '💵 Finance & Accounting',
    'Invoices'               => '💵 Finance & Accounting',
    'JournalEntry'           => '💵 Finance & Accounting',
    'Journals'               => '💵 Finance & Accounting',
    'PaymentMethod'          => '💵 Finance & Accounting',
    'Payments'               => '💵 Finance & Accounting',
    'ReconciliationItem'     => '💵 Finance & Accounting',
    'TaxConfig'              => '💵 Finance & Accounting',
    'TaxTransaction'         => '💵 Finance & Accounting',

    // 📦 Product & Inventory
    'AbcClassifications'     => '📦 Product & Inventory',
    'Batches'                => '📦 Product & Inventory',
    'Brands'                 => '📦 Product & Inventory',
    'GoodsReceiptItems'      => '📦 Product & Inventory',
    'GoodsReceipts'          => '📦 Product & Inventory',
    'ProductCategories'      => '📦 Product & Inventory',
    'Products'               => '📦 Product & Inventory',
    'ProductVariants'        => '📦 Product & Inventory',
    'PurchaseOrderItems'     => '📦 Product & Inventory',
    'PurchaseOrders'         => '📦 Product & Inventory',
    'PurchaseRequisitionItems'=> '📦 Product & Inventory',
    'PurchaseRequisitions'   => '📦 Product & Inventory',
    'QualityChecks'          => '📦 Product & Inventory',
    'SerialNumbers'          => '📦 Product & Inventory',
    'StockBalances'          => '📦 Product & Inventory',
    'StockMovements'         => '📦 Product & Inventory',
    'StockOpnameItems'       => '📦 Product & Inventory',
    'StockOpnames'           => '📦 Product & Inventory',
    'Supplier'               => '📦 Product & Inventory',
    'Units'                  => '📦 Product & Inventory',
    'Warehouse'              => '📦 Product & Inventory',

    // 📈 Sales & CRM
    'CallLogs'               => '📈 Sales & CRM',
    'Campaigns'              => '📈 Sales & CRM',
    'ChatbotFlows'           => '📈 Sales & CRM',
    'ClientContacts'         => '📈 Sales & CRM',
    'Clients'                => '📈 Sales & CRM',
    'ClientSegments'         => '📈 Sales & CRM',
    'CommissionSlabs'        => '📈 Sales & CRM',
    'Coupons'                => '📈 Sales & CRM',
    'Deals'                  => '📈 Sales & CRM',
    'EmailCampaign'          => '📈 Sales & CRM',
    'EmailLogs'              => '📈 Sales & CRM',
    'LandingPage'            => '📈 Sales & CRM',
    'LeadActivities'         => '📈 Sales & CRM',
    'Leads'                  => '📈 Sales & CRM',
    'LeadScore'              => '📈 Sales & CRM',
    'LeadSources'            => '📈 Sales & CRM',
    'MarketingAutomations'   => '📈 Sales & CRM',
    'PipelineStages'         => '📈 Sales & CRM',
    'PriceLists'             => '📈 Sales & CRM',
    'Promotions'             => '📈 Sales & CRM',
    'Quotations'             => '📈 Sales & CRM',
    'Referrals'              => '📈 Sales & CRM',
    'SalesInvoices'          => '📈 Sales & CRM',
    'SalesOrders'            => '📈 Sales & CRM',
    'SalesReturns'           => '📈 Sales & CRM',
    'SalesTargets'           => '📈 Sales & CRM',
    'WaAutoReplies'          => '📈 Sales & CRM',
    'WaBlastCampaigns'       => '📈 Sales & CRM',
    'WaBlastLogs'            => '📈 Sales & CRM',
    'WaConversations'        => '📈 Sales & CRM',
    'WaTemplates'            => '📈 Sales & CRM',

    // 📋 Project Management
    'Milestones'             => '📋 Project Management',
    'ProjectMembers'         => '📋 Project Management',
    'ProjectPhases'          => '📋 Project Management',
    'Projects'               => '📋 Project Management',
    'Sprints'                => '📋 Project Management',
    'TaskAttachments'        => '📋 Project Management',
    'TaskComments'           => '📋 Project Management',
    'TaskLabels'             => '📋 Project Management',
    'Tasks'                  => '📋 Project Management',
    'TimesheetEntries'       => '📋 Project Management',
    'Timesheets'             => '📋 Project Management',

    // 💬 Collaboration
    'BizForms'               => '💬 Collaboration',
    'CalendarEvents'         => '💬 Collaboration',
    'Calendars'              => '💬 Collaboration',
    'Chats'                  => '💬 Collaboration',
    'FormFields'             => '💬 Collaboration',
    'FormSubmissions'        => '💬 Collaboration',
    'MeetingAttendees'       => '💬 Collaboration',
    'MeetingMinutes'         => '💬 Collaboration',
    'Meetings'               => '💬 Collaboration',
    'NoticeBoards'           => '💬 Collaboration',
    'WikiCategory'           => '💬 Collaboration',
    'WikiPage'               => '💬 Collaboration',

    // 🛒 POS & Retail
    'CashierShifts'          => '🛒 POS & Retail',
    'LoyaltyConfigs'         => '🛒 POS & Retail',
    'LoyaltyTransactions'    => '🛒 POS & Retail',
    'PosMembers'             => '🛒 POS & Retail',
    'PosPayments'            => '🛒 POS & Retail',
    'PosRefunds'             => '🛒 POS & Retail',
    'PosTransactionItems'    => '🛒 POS & Retail',
    'PosTransactions'        => '🛒 POS & Retail',
    'PosVouchers'            => '🛒 POS & Retail',
    'ProductDiscounts'       => '🛒 POS & Retail',

    // 🎓 Learning
    'Certificates'           => '🎓 Learning',
    'CourseEnrollments'      => '🎓 Learning',
    'CourseLessons'          => '🎓 Learning',
    'CourseModules'          => '🎓 Learning',
    'Courses'                => '🎓 Learning',
    'QuizQuestions'          => '🎓 Learning',
    'Quizzes'                => '🎓 Learning',

    // 🏆 Gamification
    'Challenges'             => '🏆 Gamification',
    'GamificationBadge'      => '🏆 Gamification',
    'Rewards'                => '🏆 Gamification',

    // 🎫 Support
    'SlaPolicies'            => '🎫 Support',
    'Ticket'                 => '🎫 Support',
    'TicketCategory'         => '🎫 Support',
    'TicketTag'              => '🎫 Support',

    // 🤖 AI Assistant
    'AiConversations'        => '🤖 AI Assistant',
    'AiKnowledgeBases'       => '🤖 AI Assistant',
    'AiProviders'            => '🤖 AI Assistant',

    // ⚡ Automation & Workflow
    'ApprovalRequests'       => '⚡ Automation & Workflow',
    'ApprovalWorkflows'      => '⚡ Automation & Workflow',
    'BpmnProcess'            => '⚡ Automation & Workflow',
    'BpmnProcessInstance'    => '⚡ Automation & Workflow',
    'Workflows'              => '⚡ Automation & Workflow',

    // 🔗 Integrations
    'ApiKeys'                => '🔗 Integrations',
    'IntegrationConnectors'  => '🔗 Integrations',
    'Integrations'           => '🔗 Integrations',
    'SignatureProviders'     => '🔗 Integrations',
    'SmsGateway'             => '🔗 Integrations',
    'SmsLog'                 => '🔗 Integrations',
    'VirtualAccounts'        => '🔗 Integrations',
    'Webhooks'               => '🔗 Integrations',

    // 🏭 Industry (all manufacturing, healthcare, construction, hospitality, etc.)
    'Appointments'           => '🏭 Industry',
    'BillOfMaterials'        => '🏭 Industry',
    'BomItems'               => '🏭 Industry',
    'BpjsClaims'             => '🏭 Industry',
    'ColdChainLog'           => '🏭 Industry',
    'ContractedEquipmentResource' => '🏭 Industry',
    'DailySiteReports'       => '🏭 Industry',
    'DeliveryItem'           => '🏭 Industry',
    'DeliveryOrder'          => '🏭 Industry',
    'EcommerceChannel'       => '🏭 Industry',
    'EcommerceInventoryLog'  => '🏭 Industry',
    'EcommerceOrder'         => '🏭 Industry',
    'EcommerceOrderItem'     => '🏭 Industry',
    'FinishedGoods'          => '🏭 Industry',
    'FleetGpsTrack'          => '🏭 Industry',
    'GuestFolios'            => '🏭 Industry',
    'HotelServices'          => '🏭 Industry',
    'LabOrders'              => '🏭 Industry',
    'LabResults'             => '🏭 Industry',
    'Machines'               => '🏭 Industry',
    'MaintenanceRequests'    => '🏭 Industry',
    'MedicalRecords'         => '🏭 Industry',
    'Patients'               => '🏭 Industry',
    'Prescriptions'          => '🏭 Industry',
    'ProductionOrders'       => '🏭 Industry',
    'ProductionPlans'        => '🏭 Industry',
    'ProductionQcChecks'     => '🏭 Industry',
    'ProgressBillings'       => '🏭 Industry',
    'ProjectSiteInventories' => '🏭 Industry',
    'PropertyUnits'          => '🏭 Industry',
    'RabItems'               => '🏭 Industry',
    'RoomBookings'           => '🏭 Industry',
    'Rooms'                  => '🏭 Industry',
    'RoutingOperations'      => '🏭 Industry',
    'ServiceChargeInvoices'  => '🏭 Industry',
    'ServiceChecklistResource' => '🏭 Industry',
    'ServiceContractResource'=> '🏭 Industry',
    'SubcontractorContracts' => '🏭 Industry',
    'SubcontractOrders'      => '🏭 Industry',
    'TechnicianVanResource'  => '🏭 Industry',
    'TenancyContracts'       => '🏭 Industry',
    'VanInventoryResource'   => '🏭 Industry',
    'WasteLogs'              => '🏭 Industry',
    'WorkCenters'            => '🏭 Industry',
    'WorkOrderResource'      => '🏭 Industry',

    // 🌱 ESG & Sustainability
    'EnergyMeter'            => '🌱 ESG & Sustainability',
    'EnergyReading'          => '🌱 ESG & Sustainability',
    'EsgTargets'             => '🌱 ESG & Sustainability',
    'IotAlert'               => '🌱 ESG & Sustainability',
    'IotDevice'              => '🌱 ESG & Sustainability',
    'IotReading'             => '🌱 ESG & Sustainability',
    'WasteRecords'           => '🌱 ESG & Sustainability',
    'WaterUsages'            => '🌱 ESG & Sustainability',

    // 🛡️ Compliance
    'ConsentRecords'         => '🛡️ Compliance',
    'DataBreaches'           => '🛡️ Compliance',
    'DpiaAssessments'        => '🛡️ Compliance',
    'IsoAudits'              => '🛡️ Compliance',
    'IsoIncidents'           => '🛡️ Compliance',
    'IsoPolicies'            => '🛡️ Compliance',
    'IsoRisks'               => '🛡️ Compliance',
    'SodRules'               => '🛡️ Compliance',

    // 🔷 Blockchain
    'BlockchainTransaction'  => '🔷 Blockchain',
    'ProductBlockchainEvent' => '🔷 Blockchain',

    // 💳 Billing & Licensing
    'Licenses'               => '💳 Billing & Licensing',
    'SubscriptionInvoices'   => '💳 Billing & Licensing',
    'SubscriptionPayments'   => '💳 Billing & Licensing',
    'SubscriptionPlans'      => '💳 Billing & Licensing',
    'Subscriptions'          => '💳 Billing & Licensing',

    // 🧩 Platform
    'MarketplaceApps'        => '🧩 Platform',
    'MarketplaceInstalls'    => '🧩 Platform',

    // ⚙️ Sistem
    'AuditLogs'              => '⚙️ Sistem',
    'DocumentGenerations'    => '⚙️ Sistem',
    'DocumentTemplates'      => '⚙️ Sistem',
    'NotificationTemplates'  => '⚙️ Sistem',
    'Notifications'          => '⚙️ Sistem',
    'Permissions'            => '⚙️ Sistem',
    'Roles'                  => '⚙️ Sistem',
    'SystemSettings'         => '⚙️ Sistem',
    'Translations'           => '⚙️ Sistem',
];

// ─── STEP 1: Update all Resource files ──────────────────────────────────────
$resourcesPath = $basePath . '/app/Filament/Resources';
$updated = 0;
$errors = [];

foreach ($mapping as $dir => $newGroup) {
    $dirPath = $resourcesPath . '/' . $dir;
    if (!is_dir($dirPath)) {
        $errors[] = "MISSING DIR: $dir (mapped to '$newGroup')";
        continue;
    }
    
    $phpFiles = glob($dirPath . '/*Resource.php');
    if (empty($phpFiles)) {
        $errors[] = "NO RESOURCE FILE: $dir";
        continue;
    }
    
    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        
        // Handles both single-line and multi-line getNavigationGroup patterns
        // Single: function getNavigationGroup()...: string|null { return 'OLD'; }
        // Multi:  function getNavigationGroup()...: string|null\n    {\n        return 'OLD';\n    }
        $pattern = "/(function\s+getNavigationGroup\(\).*?return\s+)'[^']*'(\s*;)/s";
        
        $newContent = preg_replace($pattern, "$1'" . $newGroup . "'$2", $content, 1);
        
        if ($newContent !== $content && $newContent !== null) {
            file_put_contents($file, $newContent);
            $updated++;
            echo "  UPDATED: $dir/" . basename($file) . " -> '$newGroup'\n";
        } elseif ($newContent === $content) {
            $errors[] = "NO CHANGE: $dir (return line not matched)";
        }
    }
}

echo "\n✅ Updated $updated resource files\n";

// ─── STEP 2: Update AdminPanelProvider with new 23 NavigationGroups ─────────
$providerFile = $basePath . '/app/Providers/Filament/AdminPanelProvider.php';
$providerContent = file_get_contents($providerFile);

$newNavigationGroups = <<<'PHP'
            ->navigationGroups([
                NavigationGroup::make('🏠 Dashboard & Reporting')->collapsed(false),
                NavigationGroup::make('🏢 Organisasi')->collapsed(false),
                NavigationGroup::make('👥 Human Capital')->collapsed(false),
                NavigationGroup::make('💰 Payroll')->collapsed(false),
                NavigationGroup::make('💵 Finance & Accounting')->collapsed(false),
                NavigationGroup::make('📦 Product & Inventory')->collapsed(false),
                NavigationGroup::make('📈 Sales & CRM')->collapsed(true),
                NavigationGroup::make('📋 Project Management')->collapsed(true),
                NavigationGroup::make('💬 Collaboration')->collapsed(true),
                NavigationGroup::make('🛒 POS & Retail')->collapsed(true),
                NavigationGroup::make('🎓 Learning')->collapsed(true),
                NavigationGroup::make('🏆 Gamification')->collapsed(true),
                NavigationGroup::make('🎫 Support')->collapsed(true),
                NavigationGroup::make('🤖 AI Assistant')->collapsed(true),
                NavigationGroup::make('⚡ Automation & Workflow')->collapsed(true),
                NavigationGroup::make('🔗 Integrations')->collapsed(true),
                NavigationGroup::make('🏭 Industry')->collapsed(true),
                NavigationGroup::make('🌱 ESG & Sustainability')->collapsed(true),
                NavigationGroup::make('🛡️ Compliance')->collapsed(true),
                NavigationGroup::make('🔷 Blockchain')->collapsed(true),
                NavigationGroup::make('💳 Billing & Licensing')->collapsed(true),
                NavigationGroup::make('🧩 Platform')->collapsed(true),
                NavigationGroup::make('⚙️ Sistem')->collapsed(true),
            ])
PHP;

// Replace the existing navigationGroups block
$providerContent = preg_replace(
    '/->navigationGroups\(\[.*?\]\)/s',
    $newNavigationGroups,
    $providerContent,
    1
);

if ($providerContent !== null) {
    file_put_contents($providerFile, $providerContent);
    echo "✅ Updated AdminPanelProvider with 23 new NavigationGroups\n";
} else {
    $errors[] = "FAILED to update AdminPanelProvider";
}

// ─── REPORT ─────────────────────────────────────────────────────────────────
if (!empty($errors)) {
    echo "\n⚠️ Warnings/Errors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\nDone.\n";

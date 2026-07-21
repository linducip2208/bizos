/**
 * BizOS Screenshot Capture
 * 
 * Logs in as admin, captures all Filament admin pages at 1440x900.
 * Saves PNGs to public/marketing/screens/
 */

const { chromium } = require('playwright');
const path = require('path');

const BASE = 'http://bizos.test';
const OUT_DIR = path.join(__dirname, '..', 'public', 'marketing', 'screens');
const EMAIL = 'admin@bizos.id';
const PASSWORD = 'password';

const pages = [
    // Public
    { url: '/admin/login', file: '01-login.png', auth: false },

    // Dashboard
    { url: '/admin', file: '02-dashboard.png', auth: true },

    // Master Data
    { url: '/admin/companies', file: '03-companies.png', auth: true },
    { url: '/admin/branches', file: '04-branches.png', auth: true },
    { url: '/admin/departments', file: '05-departments.png', auth: true },
    { url: '/admin/positions', file: '06-positions.png', auth: true },
    { url: '/admin/designations', file: '07-designations.png', auth: true },
    { url: '/admin/grades', file: '08-grades.png', auth: true },
    { url: '/admin/employees', file: '09-employees.png', auth: true },

    // HRM - Attendance
    { url: '/admin/shifts', file: '10-shifts.png', auth: true },
    { url: '/admin/attendances', file: '11-attendances.png', auth: true },
    { url: '/admin/attendance-configs', file: '12-attendance-configs.png', auth: true },
    { url: '/admin/wifi-access-points', file: '13-wifi-access-points.png', auth: true },

    // HRM - Leave & Overtime
    { url: '/admin/leave-types', file: '14-leave-types.png', auth: true },
    { url: '/admin/leaves', file: '15-leaves.png', auth: true },
    { url: '/admin/overtimes', file: '16-overtimes.png', auth: true },

    // HRM - Reimbursement & Visit
    { url: '/admin/reimbursement-categories', file: '17-reimbursement-categories.png', auth: true },
    { url: '/admin/reimbursements', file: '18-reimbursements.png', auth: true },
    { url: '/admin/visits', file: '19-visits.png', auth: true },

    // HRM - Recruitment
    { url: '/admin/job-postings', file: '20-job-postings.png', auth: true },
    { url: '/admin/candidates', file: '21-candidates.png', auth: true },
    { url: '/admin/interviews', file: '22-interviews.png', auth: true },
    { url: '/admin/interviewers', file: '23-interviewers.png', auth: true },
    { url: '/admin/interview-results', file: '24-interview-results.png', auth: true },

    // HRM - Feedback
    { url: '/admin/feedback-cycles', file: '25-feedback-cycles.png', auth: true },
    { url: '/admin/feedback-questions', file: '26-feedback-questions.png', auth: true },
    { url: '/admin/feedback-reviewers', file: '27-feedback-reviewers.png', auth: true },
    { url: '/admin/feedback-answers', file: '28-feedback-answers.png', auth: true },

    // HRM - Canteen
    { url: '/admin/canteen-menus', file: '29-canteen-menus.png', auth: true },
    { url: '/admin/canteen-orders', file: '30-canteen-orders.png', auth: true },
    { url: '/admin/canteen-order-items', file: '31-canteen-order-items.png', auth: true },

    // HRM - Announcement
    { url: '/admin/announcements', file: '32-announcements.png', auth: true },

    // Payroll
    { url: '/admin/salary-components', file: '33-salary-components.png', auth: true },
    { url: '/admin/employee-salary-components', file: '34-employee-salary-components.png', auth: true },
    { url: '/admin/payroll-periods', file: '35-payroll-periods.png', auth: true },
    { url: '/admin/payrolls', file: '36-payrolls.png', auth: true },
    { url: '/admin/payroll-items', file: '37-payroll-items.png', auth: true },
    { url: '/admin/pay-slips', file: '38-pay-slips.png', auth: true },
    { url: '/admin/pph21-configs', file: '39-pph21-configs.png', auth: true },
    { url: '/admin/bpjs-configs', file: '40-bpjs-configs.png', auth: true },
    { url: '/admin/thr-configs', file: '41-thr-configs.png', auth: true },

    // Finance - COA & Journal
    { url: '/admin/coa-categories', file: '42-coa-categories.png', auth: true },
    { url: '/admin/coas', file: '43-coa.png', auth: true },
    { url: '/admin/coa-balances', file: '44-coa-balances.png', auth: true },
    { url: '/admin/journals', file: '45-journals.png', auth: true },
    { url: '/admin/journal-entries', file: '46-journal-entries.png', auth: true },

    // Finance - Invoice & Payment
    { url: '/admin/invoices', file: '47-invoices.png', auth: true },
    { url: '/admin/invoice-items', file: '48-invoice-items.png', auth: true },
    { url: '/admin/payment-methods', file: '49-payment-methods.png', auth: true },
    { url: '/admin/payments', file: '50-payments.png', auth: true },
    { url: '/admin/invoice-payments', file: '51-invoice-payments.png', auth: true },

    // Finance - Budget & Tax
    { url: '/admin/budgets', file: '52-budgets.png', auth: true },
    { url: '/admin/budget-items', file: '53-budget-items.png', auth: true },
    { url: '/admin/tax-configs', file: '54-tax-configs.png', auth: true },
    { url: '/admin/tax-transactions', file: '55-tax-transactions.png', auth: true },

    // Finance - Asset
    { url: '/admin/asset-categories', file: '56-asset-categories.png', auth: true },
    { url: '/admin/assets', file: '57-assets.png', auth: true },
    { url: '/admin/asset-depreciations', file: '58-asset-depreciations.png', auth: true },
    { url: '/admin/asset-mutations', file: '59-asset-mutations.png', auth: true },
    { url: '/admin/asset-maintenances', file: '60-asset-maintenances.png', auth: true },

    // CRM
    { url: '/admin/lead-sources', file: '61-lead-sources.png', auth: true },
    { url: '/admin/leads', file: '62-leads.png', auth: true },
    { url: '/admin/lead-activities', file: '63-lead-activities.png', auth: true },
    { url: '/admin/clients', file: '64-clients.png', auth: true },
    { url: '/admin/client-contacts', file: '65-client-contacts.png', auth: true },
    { url: '/admin/client-segments', file: '66-client-segments.png', auth: true },
    { url: '/admin/pipeline-stages', file: '67-pipeline-stages.png', auth: true },
    { url: '/admin/deals', file: '68-deals.png', auth: true },
    { url: '/admin/wa-templates', file: '69-wa-templates.png', auth: true },
    { url: '/admin/wa-blast-campaigns', file: '70-wa-blast-campaigns.png', auth: true },
    { url: '/admin/wa-blast-logs', file: '71-wa-blast-logs.png', auth: true },
    { url: '/admin/wa-auto-replies', file: '72-wa-auto-replies.png', auth: true },
    { url: '/admin/wa-conversations', file: '73-wa-conversations.png', auth: true },

    // Project Management
    { url: '/admin/projects', file: '74-projects.png', auth: true },
    { url: '/admin/project-phases', file: '75-project-phases.png', auth: true },
    { url: '/admin/project-members', file: '76-project-members.png', auth: true },
    { url: '/admin/tasks', file: '77-tasks.png', auth: true },
    { url: '/admin/task-comments', file: '78-task-comments.png', auth: true },
    { url: '/admin/task-attachments', file: '79-task-attachments.png', auth: true },
    { url: '/admin/task-labels', file: '80-task-labels.png', auth: true },
    { url: '/admin/milestones', file: '81-milestones.png', auth: true },
    { url: '/admin/timesheets', file: '82-timesheets.png', auth: true },
    { url: '/admin/timesheet-entries', file: '83-timesheet-entries.png', auth: true },

    // POS
    { url: '/admin/product-categories', file: '84-product-categories.png', auth: true },
    { url: '/admin/products', file: '85-products.png', auth: true },
    { url: '/admin/product-variants', file: '86-product-variants.png', auth: true },
    { url: '/admin/product-discounts', file: '87-product-discounts.png', auth: true },
    { url: '/admin/pos-members', file: '88-pos-members.png', auth: true },
    { url: '/admin/pos-vouchers', file: '89-pos-vouchers.png', auth: true },
    { url: '/admin/cashier-shifts', file: '90-cashier-shifts.png', auth: true },
    { url: '/admin/pos-transactions', file: '91-pos-transactions.png', auth: true },
    { url: '/admin/pos-transaction-items', file: '92-pos-transaction-items.png', auth: true },
    { url: '/admin/pos-payments', file: '93-pos-payments.png', auth: true },
    { url: '/admin/pos-refunds', file: '94-pos-refunds.png', auth: true },

    // Collaboration
    { url: '/admin/chats', file: '95-chats.png', auth: true },
    { url: '/admin/meetings', file: '96-meetings.png', auth: true },
    { url: '/admin/meeting-attendees', file: '97-meeting-attendees.png', auth: true },
    { url: '/admin/meeting-minutes', file: '98-meeting-minutes.png', auth: true },
    { url: '/admin/calendars', file: '99-calendars.png', auth: true },
    { url: '/admin/calendar-events', file: '100-calendar-events.png', auth: true },
    { url: '/admin/forms', file: '101-forms.png', auth: true },
    { url: '/admin/form-fields', file: '102-form-fields.png', auth: true },
    { url: '/admin/form-submissions', file: '103-form-submissions.png', auth: true },

    // LMS
    { url: '/admin/courses', file: '104-courses.png', auth: true },
    { url: '/admin/course-modules', file: '105-course-modules.png', auth: true },
    { url: '/admin/course-lessons', file: '106-course-lessons.png', auth: true },
    { url: '/admin/course-enrollments', file: '107-course-enrollments.png', auth: true },
    { url: '/admin/quizzes', file: '108-quizzes.png', auth: true },
    { url: '/admin/quiz-questions', file: '109-quiz-questions.png', auth: true },

    // AI Assistant
    { url: '/admin/ai-providers', file: '110-ai-providers.png', auth: true },
    { url: '/admin/ai-conversations', file: '111-ai-conversations.png', auth: true },
    { url: '/admin/ai-knowledge-bases', file: '112-ai-knowledge-bases.png', auth: true },

    // Reports
    { url: '/admin/laporan-bisnis', file: '113-laporan-bisnis.png', auth: true },
    { url: '/admin/laporan-keuangan', file: '114-laporan-keuangan.png', auth: true },
    { url: '/admin/laporan-operasional', file: '115-laporan-operasional.png', auth: true },

    // System
    { url: '/admin/roles', file: '116-roles.png', auth: true },
    { url: '/admin/permissions', file: '117-permissions.png', auth: true },
    { url: '/admin/notification-templates', file: '118-notification-templates.png', auth: true },
    { url: '/admin/notifications', file: '119-notifications.png', auth: true },
    { url: '/admin/audit-logs', file: '120-audit-logs.png', auth: true },
    { url: '/admin/system-settings', file: '121-system-settings.png', auth: true },
    { url: '/admin/integrations', file: '122-integrations.png', auth: true },
];

(async () => {
    console.log('Launching browser...');
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        deviceScaleFactor: 1.5,
    });
    const page = await context.newPage();

    // Login
    console.log('Logging in as admin...');
    await page.goto(`${BASE}/admin/login`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('input[type="email"]', { timeout: 10000 });
    
    // Type values with delay to trigger Livewire
    await page.fill('input[type="email"]', '');
    await page.type('input[type="email"]', EMAIL, { delay: 100 });
    await page.fill('input[type="password"]', '');
    await page.type('input[type="password"]', PASSWORD, { delay: 100 });
    
    await page.waitForTimeout(1000);
    
    // Click submit and wait for redirect
    await Promise.all([
        page.waitForURL('**/admin', { timeout: 15000 }).catch(() => {}),
        page.locator('button[type="submit"]').click(),
    ]);
    
    await page.waitForTimeout(2000);
    await page.waitForLoadState('networkidle').catch(() => {});

    const url = page.url();
    if (url.includes('/login')) {
        console.error(`FAILED to login. Current URL: ${url}`);
        await browser.close();
        process.exit(1);
    }
    console.log(`Login success! URL: ${url}`);

    let success = 0;
    let failed = 0;

    for (const entry of pages) {
        const { url: pageUrl, file, auth } = entry;
        const fullUrl = `${BASE}${pageUrl}`;
        const outPath = path.join(OUT_DIR, file);

        try {
            if (auth) {
                await page.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 15000 });
                await page.waitForTimeout(2000);
            } else {
                // Create a new incognito page without auth
                const anonContext = await browser.newContext({
                    viewport: { width: 1440, height: 900 },
                    deviceScaleFactor: 1.5,
                });
                const anonPage = await anonContext.newPage();
                await anonPage.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 15000 });
                await anonPage.waitForTimeout(2000);
                await anonPage.screenshot({ path: outPath, fullPage: false });
                await anonContext.close();
                console.log(`[${++success}/${pages.length}] ${file}`);
                continue;
            }

            // Check if we got a 404 or error page
            if (page.url().includes('/login')) {
                console.log(`[SKIP] ${file} — redirected to login (no access)`);
                continue;
            }

            await page.screenshot({ path: outPath, fullPage: false });
            console.log(`[${++success}/${pages.length}] ${file}`);
        } catch (err) {
            console.log(`[FAIL] ${file} — ${err.message}`);
            failed++;
        }
    }

    console.log(`\nDone! Success: ${success}, Failed: ${failed}`);
    await browser.close();
})();

<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin');
    }
    return view('marketing');
})->name('home');

Route::get('/docs', [App\Http\Controllers\DocsController::class, 'index'])->name('docs');
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index']);
Route::get('/sitemap/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index']);
Route::get('/robots.txt', [App\Http\Controllers\SitemapController::class, 'robots']);

Route::get('/best-hrm-software', [App\Http\Controllers\ProgrammaticSeoController::class, 'bestHrm']);
Route::get('/best-accounting-software-indonesia', [App\Http\Controllers\ProgrammaticSeoController::class, 'bestAccounting']);
Route::get('/best-payroll-software-indonesia', [App\Http\Controllers\ProgrammaticSeoController::class, 'bestPayroll']);
Route::get('/best-crm-software-indonesia', [App\Http\Controllers\ProgrammaticSeoController::class, 'bestCrm']);
Route::get('/best-project-management-software', [App\Http\Controllers\ProgrammaticSeoController::class, 'bestProject']);
Route::get('/compare/bizos-vs-spreadsheet', [App\Http\Controllers\ProgrammaticSeoController::class, 'compareVsSpreadsheet']);
Route::get('/compare/bizos-vs-talenta', [App\Http\Controllers\ProgrammaticSeoController::class, 'compareVsTalenta']);
Route::get('/compare/bizos-vs-jurnal', [App\Http\Controllers\ProgrammaticSeoController::class, 'compareVsJurnal']);
Route::get('/alternatives-to-excel-for-hr', [App\Http\Controllers\ProgrammaticSeoController::class, 'alternativesExcel']);
Route::get('/alternatives-to-talenta', [App\Http\Controllers\ProgrammaticSeoController::class, 'alternativesTalenta']);

/*==========================================================================
 * Employee Self-Service Portal
 *========================================================================*/
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/login', [App\Http\Controllers\Portal\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\Portal\AuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\Portal\AuthController::class, 'logout'])->name('logout');

    Route::get('/forgot-password', [App\Http\Controllers\Portal\AuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/forgot-password', [App\Http\Controllers\Portal\AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [App\Http\Controllers\Portal\AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\Portal\AuthController::class, 'resetPassword'])->name('password.update');

    Route::middleware('auth:web')->group(function () {
        Route::get('/', [App\Http\Controllers\Portal\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/invoices', [App\Http\Controllers\Portal\DashboardController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{id}', [App\Http\Controllers\Portal\DashboardController::class, 'invoiceDetail'])->name('invoice-detail');

        Route::get('/pin-setup', [App\Http\Controllers\Portal\AuthController::class, 'showPinSetup'])->name('pin.setup');
        Route::post('/pin-setup', [App\Http\Controllers\Portal\AuthController::class, 'setupPin'])->name('pin.store');
        Route::delete('/pin', [App\Http\Controllers\Portal\AuthController::class, 'removePin'])->name('pin.remove');

        /* Attendance */
        Route::get('/attendance', [App\Http\Controllers\Portal\AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/clock-in', [App\Http\Controllers\Portal\AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
        Route::post('/attendance/clock-out', [App\Http\Controllers\Portal\AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
        Route::get('/attendance/today', [App\Http\Controllers\Portal\AttendanceController::class, 'todayStatus'])->name('attendance.today');

        /* Leaves */
        Route::get('/leaves', [App\Http\Controllers\Portal\LeaveController::class, 'index'])->name('leave.index');
        Route::get('/leaves/create', [App\Http\Controllers\Portal\LeaveController::class, 'create'])->name('leave.create');
        Route::post('/leaves', [App\Http\Controllers\Portal\LeaveController::class, 'store'])->name('leave.store');
        Route::get('/leaves/{id}', [App\Http\Controllers\Portal\LeaveController::class, 'show'])->name('leave.show');

        /* Overtimes */
        Route::get('/overtimes', [App\Http\Controllers\Portal\OvertimeController::class, 'index'])->name('overtime.index');
        Route::get('/overtimes/create', [App\Http\Controllers\Portal\OvertimeController::class, 'create'])->name('overtime.create');
        Route::post('/overtimes', [App\Http\Controllers\Portal\OvertimeController::class, 'store'])->name('overtime.store');
        Route::get('/overtimes/{id}', [App\Http\Controllers\Portal\OvertimeController::class, 'show'])->name('overtime.show');

        /* Reimbursements */
        Route::get('/reimbursements', [App\Http\Controllers\Portal\ReimbursementController::class, 'index'])->name('reimbursement.index');
        Route::get('/reimbursements/create', [App\Http\Controllers\Portal\ReimbursementController::class, 'create'])->name('reimbursement.create');
        Route::post('/reimbursements', [App\Http\Controllers\Portal\ReimbursementController::class, 'store'])->name('reimbursement.store');
        Route::get('/reimbursements/{id}', [App\Http\Controllers\Portal\ReimbursementController::class, 'show'])->name('reimbursement.show');

        /* PaySlips */
        Route::get('/payslips', [App\Http\Controllers\Portal\PaySlipController::class, 'index'])->name('payslip.index');
        Route::get('/payslips/{id}/download', [App\Http\Controllers\Portal\PaySlipController::class, 'download'])->name('payslip.download');

        /* Profile */
        Route::get('/profile', [App\Http\Controllers\Portal\ProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile', [App\Http\Controllers\Portal\ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [App\Http\Controllers\Portal\ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/documents', [App\Http\Controllers\Portal\ProfileController::class, 'uploadDocument'])->name('profile.document.upload');
        Route::delete('/profile/documents/{id}', [App\Http\Controllers\Portal\ProfileController::class, 'deleteDocument'])->name('profile.document.delete');
        Route::post('/profile/family', [App\Http\Controllers\Portal\ProfileController::class, 'addFamilyMember'])->name('profile.family.add');
        Route::delete('/profile/family/{id}', [App\Http\Controllers\Portal\ProfileController::class, 'removeFamilyMember'])->name('profile.family.remove');

        /* Tickets */
        Route::get('/tickets', [App\Http\Controllers\Portal\TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [App\Http\Controllers\Portal\TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [App\Http\Controllers\Portal\TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{id}', [App\Http\Controllers\Portal\TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{id}/reply', [App\Http\Controllers\Portal\TicketController::class, 'reply'])->name('tickets.reply');
    });
});

/*==========================================================================
 * Supplier Portal
 *========================================================================*/
Route::prefix('supplier')->name('supplier.')->group(function () {
    Route::get('/login', [App\Http\Controllers\SupplierPortal\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\SupplierPortal\AuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\SupplierPortal\AuthController::class, 'logout'])->name('logout');
    Route::get('/register', [App\Http\Controllers\SupplierPortal\AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [App\Http\Controllers\SupplierPortal\AuthController::class, 'register']);
    Route::get('/forgot-password', [App\Http\Controllers\SupplierPortal\AuthController::class, 'showForgotPassword'])->name('forgot-password');

    Route::middleware('auth:supplier')->group(function () {
        Route::get('/', [App\Http\Controllers\SupplierPortal\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/purchase-orders', [App\Http\Controllers\SupplierPortal\PurchaseOrderController::class, 'index'])->name('po.index');
        Route::get('/purchase-orders/{id}', [App\Http\Controllers\SupplierPortal\PurchaseOrderController::class, 'show'])->name('po.show');
        Route::post('/purchase-orders/{id}/status', [App\Http\Controllers\SupplierPortal\PurchaseOrderController::class, 'updateStatus'])->name('po.status');
        Route::post('/purchase-orders/{id}/invoice', [App\Http\Controllers\SupplierPortal\PurchaseOrderController::class, 'uploadInvoice'])->name('po.invoice');
    });
});

/*==========================================================================
 * Customer / Client Portal
 *========================================================================*/
Route::prefix('client')->name('client.')->group(function () {
    Route::get('/login', [App\Http\Controllers\ClientPortal\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\ClientPortal\AuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\ClientPortal\AuthController::class, 'logout'])->name('logout');
    Route::get('/register', [App\Http\Controllers\ClientPortal\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [App\Http\Controllers\ClientPortal\AuthController::class, 'register']);
    Route::get('/forgot-password', [App\Http\Controllers\ClientPortal\AuthController::class, 'showForgotPassword'])->name('forgot-password');

    Route::middleware('auth:client')->group(function () {
        Route::get('/', [App\Http\Controllers\ClientPortal\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/invoices', [App\Http\Controllers\ClientPortal\DashboardController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{id}', [App\Http\Controllers\ClientPortal\DashboardController::class, 'invoiceDetail'])->name('invoice-detail');
        Route::get('/deals', [App\Http\Controllers\ClientPortal\DealController::class, 'index'])->name('deals');
        Route::get('/deals/{id}', [App\Http\Controllers\ClientPortal\DealController::class, 'show'])->name('deals.show');
        Route::get('/tickets', [App\Http\Controllers\ClientPortal\TicketController::class, 'index'])->name('tickets');
        Route::get('/tickets/create', [App\Http\Controllers\ClientPortal\TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [App\Http\Controllers\ClientPortal\TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{id}', [App\Http\Controllers\ClientPortal\TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{id}/reply', [App\Http\Controllers\ClientPortal\TicketController::class, 'reply'])->name('tickets.reply');
        Route::get('/profile', [App\Http\Controllers\ClientPortal\DashboardController::class, 'profile'])->name('profile');
    });
});

Route::get('/wiki', [App\Http\Controllers\WikiController::class, 'index'])->name('wiki.index');
Route::get('/wiki/{slug}', [App\Http\Controllers\WikiController::class, 'show'])->name('wiki.show');

Route::get('/landing/{slug}', [App\Http\Controllers\LandingPageController::class, 'show'])->name('landing-page.show');
Route::get('/email/track/open/{token}', [App\Http\Controllers\EmailTrackingController::class, 'open']);
Route::get('/email/track/click/{token}', [App\Http\Controllers\EmailTrackingController::class, 'click']);

Route::post('/api/webhooks/signature', [App\Http\Controllers\Webhook\SignatureWebhookController::class, 'handle'])->name('webhook.signature');

Route::middleware('auth:web')->group(function () {
    Route::get('/receipt-scanner', function () {
        $employeeId = auth()->user()?->employee_id;
        $employee = $employeeId ? \App\Models\Employee::find($employeeId) : null;
        $departmentId = $employee?->department_id;
        return view('receipt-scanner', compact('employeeId', 'departmentId'));
    })->name('receipt.scanner');
});

// WhatsApp Business API webhook
Route::prefix('webhooks/wa')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\WhatsAppWebhookController::class, 'verify']);
    Route::post('/', [App\Http\Controllers\Api\WhatsAppWebhookController::class, 'receive']);
});

// No-Code Automation Studio
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/studio/{workflowId?}', \App\Http\Livewire\StudioBuilder::class)->name('studio.builder');
});

// BI Embed Route
Route::get('/api/bi/embed/{token}', fn(string $token) => response()->json(app(\App\Services\AdvancedBiService::class)->getEmbedData($token)));

require base_path('routes/pair-routes.php');

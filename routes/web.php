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

Route::prefix('portal')->group(function () {
    Route::get('/login', [App\Http\Controllers\Portal\AuthController::class, 'showLogin'])->name('portal.login');
    Route::post('/login', [App\Http\Controllers\Portal\AuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\Portal\AuthController::class, 'logout'])->name('portal.logout');
    Route::middleware('auth:web')->group(function () {
        Route::get('/', [App\Http\Controllers\Portal\DashboardController::class, 'index'])->name('portal.dashboard');
        Route::get('/invoices', [App\Http\Controllers\Portal\DashboardController::class, 'invoices'])->name('portal.invoices');
        Route::get('/invoices/{id}', [App\Http\Controllers\Portal\DashboardController::class, 'invoiceDetail'])->name('portal.invoice-detail');
        Route::get('/tickets', [App\Http\Controllers\Portal\TicketController::class, 'index'])->name('portal.tickets.index');
        Route::get('/tickets/create', [App\Http\Controllers\Portal\TicketController::class, 'create'])->name('portal.tickets.create');
        Route::post('/tickets', [App\Http\Controllers\Portal\TicketController::class, 'store'])->name('portal.tickets.store');
        Route::get('/tickets/{id}', [App\Http\Controllers\Portal\TicketController::class, 'show'])->name('portal.tickets.show');
        Route::post('/tickets/{id}/reply', [App\Http\Controllers\Portal\TicketController::class, 'reply'])->name('portal.tickets.reply');
    });
});

Route::get('/landing/{slug}', [App\Http\Controllers\LandingPageController::class, 'show'])->name('landing-page.show');
Route::get('/email/track/open/{token}', [App\Http\Controllers\EmailTrackingController::class, 'open']);
Route::get('/email/track/click/{token}', [App\Http\Controllers\EmailTrackingController::class, 'click']);

require base_path('routes/pair-routes.php');

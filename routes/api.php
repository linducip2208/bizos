<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Resources\EmployeeApiController;
use App\Http\Controllers\Api\Resources\AttendanceApiController;
use App\Http\Controllers\Api\Resources\LeaveApiController;
use App\Http\Controllers\Api\Resources\InvoiceApiController;
use App\Http\Controllers\Api\Resources\PaymentApiController;
use App\Http\Controllers\Api\Resources\JournalApiController;
use App\Http\Controllers\Api\Resources\ClientApiController;
use App\Http\Controllers\Api\Resources\LeadApiController;
use App\Http\Controllers\Api\Resources\DealApiController;
use App\Http\Controllers\Api\Resources\ProductApiController;
use App\Http\Controllers\Api\Resources\PosTransactionApiController;
use App\Http\Controllers\Api\Resources\ProjectApiController;
use App\Http\Controllers\Api\Resources\TaskApiController;
use App\Http\Controllers\Api\Resources\TimesheetApiController;
use App\Http\Controllers\Api\Resources\TicketApiController;
use App\Http\Controllers\Api\Resources\PayrollApiController;
use App\Http\Middleware\Api\ApiKeyMiddleware;
use App\Http\Middleware\Api\ApiRateLimitMiddleware;

Route::prefix('v1')->middleware([ApiRateLimitMiddleware::class, ApiKeyMiddleware::class])->group(function () {
    Route::apiResource('employees', EmployeeApiController::class);
    Route::apiResource('attendances', AttendanceApiController::class);
    Route::apiResource('leaves', LeaveApiController::class);
    Route::apiResource('invoices', InvoiceApiController::class);
    Route::apiResource('payments', PaymentApiController::class);
    Route::apiResource('journals', JournalApiController::class);
    Route::apiResource('clients', ClientApiController::class);
    Route::apiResource('leads', LeadApiController::class);
    Route::apiResource('deals', DealApiController::class);
    Route::apiResource('products', ProductApiController::class);
    Route::apiResource('pos-transactions', PosTransactionApiController::class);
    Route::apiResource('projects', ProjectApiController::class);
    Route::apiResource('tasks', TaskApiController::class);
    Route::apiResource('timesheets', TimesheetApiController::class);
    Route::apiResource('tickets', TicketApiController::class);
    Route::apiResource('payrolls', PayrollApiController::class);
});

/* Mobile App Routes — Sanctum Token Auth */
Route::prefix('v1/mobile')->group(function () {
    Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [App\Http\Controllers\Api\AuthController::class, 'me']);
        Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::post('/pin/setup', [App\Http\Controllers\Api\AuthController::class, 'setupPin']);
        Route::post('/pin/remove', [App\Http\Controllers\Api\AuthController::class, 'removePin']);
        Route::post('/password/change', [App\Http\Controllers\Api\AuthController::class, 'updatePassword']);
        Route::post('/register-device', [App\Http\Controllers\Api\AuthController::class, 'registerDeviceToken']);

        Route::get('/dashboard', [App\Http\Controllers\Api\DashboardController::class, 'index']);

        Route::post('/attendance/clock-in', [App\Http\Controllers\Api\AttendanceController::class, 'clockIn']);
        Route::post('/attendance/clock-out', [App\Http\Controllers\Api\AttendanceController::class, 'clockOut']);
        Route::get('/attendance/history', [App\Http\Controllers\Api\AttendanceController::class, 'history']);
        Route::get('/attendance/today', [App\Http\Controllers\Api\AttendanceController::class, 'today']);

        Route::get('/leaves', [App\Http\Controllers\Api\LeaveController::class, 'index']);
        Route::post('/leaves', [App\Http\Controllers\Api\LeaveController::class, 'store']);
        Route::get('/leaves/{id}', [App\Http\Controllers\Api\LeaveController::class, 'show']);
        Route::get('/leave-balances', [App\Http\Controllers\Api\LeaveController::class, 'balances']);
        Route::get('/leave-types', [App\Http\Controllers\Api\LeaveController::class, 'types']);

        Route::get('/notifications', [App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
        Route::get('/notifications/unread-count', [App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);

        Route::get('/profile', [App\Http\Controllers\Api\ProfileController::class, 'show']);
        Route::post('/profile', [App\Http\Controllers\Api\ProfileController::class, 'update']);
        Route::post('/profile/photo', [App\Http\Controllers\Api\ProfileController::class, 'uploadPhoto']);
        Route::get('/payslips', [App\Http\Controllers\Api\ProfileController::class, 'payslips']);
        Route::get('/payslips/{id}/pdf', [App\Http\Controllers\Api\ProfileController::class, 'payslipPdf']);

        // Mobile Home — complete home screen data in one call
        Route::get('/home', [App\Http\Controllers\Api\DashboardController::class, 'home']);

        // Offline Mode
        Route::post('/sync', [App\Http\Controllers\Api\OfflineController::class, 'sync']);
        Route::get('/offline-data', [App\Http\Controllers\Api\OfflineController::class, 'offlineData']);
        Route::get('/check-updates', [App\Http\Controllers\Api\OfflineController::class, 'checkUpdates']);
        Route::get('/pending-actions', [App\Http\Controllers\Api\OfflineController::class, 'pendingActions']);
        Route::post('/resolve-conflict', [App\Http\Controllers\Api\OfflineController::class, 'resolveConflict']);

        // Biometric Auth
        Route::post('/biometric/register', [App\Http\Controllers\Api\BiometricController::class, 'register']);
        Route::post('/biometric/verify', [App\Http\Controllers\Api\BiometricController::class, 'verify']);
        Route::post('/biometric/challenge', [App\Http\Controllers\Api\BiometricController::class, 'getChallenge']);
        Route::delete('/biometric/revoke', [App\Http\Controllers\Api\BiometricController::class, 'revoke']);
        Route::get('/biometric/status', [App\Http\Controllers\Api\BiometricController::class, 'status']);
        Route::get('/biometric/devices', [App\Http\Controllers\Api\BiometricController::class, 'devices']);

        // Barcode Scanner
        Route::get('/scan/{barcode}', [App\Http\Controllers\Api\BarcodeController::class, 'lookup']);
        Route::post('/scan/receive', [App\Http\Controllers\Api\BarcodeController::class, 'receive']);
        Route::post('/scan/pick', [App\Http\Controllers\Api\BarcodeController::class, 'pick']);
        Route::post('/scan/opname', [App\Http\Controllers\Api\BarcodeController::class, 'opname']);
        Route::get('/scan/label/{product_id}', [App\Http\Controllers\Api\BarcodeController::class, 'label'])->whereNumber('product_id');
        Route::post('/scan/labels', [App\Http\Controllers\Api\BarcodeController::class, 'labels']);

        // Voice Notes
        Route::post('/voice-note', [App\Http\Controllers\Api\VoiceNoteController::class, 'upload']);
        Route::get('/voice-notes', [App\Http\Controllers\Api\VoiceNoteController::class, 'index']);
        Route::get('/voice-notes/{id}/audio', [App\Http\Controllers\Api\VoiceNoteController::class, 'audio'])
            ->whereNumber('id')
            ->name('api.mobile.voice-note.audio');
        Route::post('/voice-notes/{id}/played', [App\Http\Controllers\Api\VoiceNoteController::class, 'markPlayed'])->whereNumber('id');
        Route::post('/voice-notes/{id}/transcribe', [App\Http\Controllers\Api\VoiceNoteController::class, 'transcribe'])->whereNumber('id');
        Route::get('/voice-notes/unplayed-count', [App\Http\Controllers\Api\VoiceNoteController::class, 'unplayedCount']);

        // Push-to-Talk Channels
        Route::post('/ptt/channel', [App\Http\Controllers\Api\VoiceNoteController::class, 'createChannel']);
        Route::get('/ptt/channels', [App\Http\Controllers\Api\VoiceNoteController::class, 'getUserChannels']);
        Route::post('/ptt/channel/{id}/broadcast', [App\Http\Controllers\Api\VoiceNoteController::class, 'broadcastChannel'])->whereNumber('id');
        Route::delete('/ptt/channel/{id}/leave', [App\Http\Controllers\Api\VoiceNoteController::class, 'leaveChannel'])->whereNumber('id');
    });
});

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => config('app.name') . ' API v1',
        'documentation' => url('/docs/api'),
        'endpoints' => url('/api/v1'),
    ]);
});

Route::get('docs', function () {
    $service = app(\App\Services\ApiDocumentationService::class);
    return response($service->generateHtmlDocs())->header('Content-Type', 'text/html');
});

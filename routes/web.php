<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\CommandCenterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\MailLogController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SmsLogController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Public legal / corporate pages (required for iyzico merchant onboarding)
Route::get('/hakkimizda', [LegalController::class, 'about'])->name('legal.about');
Route::get('/iletisim', [LegalController::class, 'contact'])->name('legal.contact');
Route::get('/gizlilik-politikasi', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/mesafeli-satis-sozlesmesi', [LegalController::class, 'distanceSales'])->name('legal.distance-sales');
Route::get('/teslimat-ve-iade', [LegalController::class, 'deliveryReturns'])->name('legal.delivery-returns');

// Public invitation acceptance (guest — creates a user in the inviting tenant)
Route::get('/invitations/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
Route::post('/invitations/{token}', [InvitationController::class, 'storeAccepted'])->name('invitations.accept.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Onboarding wizard (shown right after registration)
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    // Resource routes (CRUD pages still exist, just not in nav)
    Route::resource('customers', CustomerController::class)->except('show');
    Route::resource('receivables', ReceivableController::class)->except('show');
    Route::resource('tasks', TaskController::class)->except('show');

    // Log pages
    Route::get('/sms-logs', [SmsLogController::class, 'index'])->name('sms-logs.index');
    Route::get('/mail-logs', [MailLogController::class, 'index'])->name('mail-logs.index');

    // Reports (viewable by any member)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Plan & usage (viewable by any member)
    Route::get('/plan', [PlanController::class, 'index'])->name('plan.index');

    // Owner-only settings: team, invitations, notification templates
    Route::middleware('owner')->group(function () {
        Route::get('/team', [TeamController::class, 'index'])->name('team.index');
        Route::patch('/team/members/{user}', [TeamController::class, 'updateMember'])->name('team.members.update');
        Route::delete('/team/members/{user}', [TeamController::class, 'destroyMember'])->name('team.members.destroy');
        Route::post('/team/invitations', [InvitationController::class, 'store'])->name('invitations.store');
        Route::delete('/team/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');

        Route::get('/templates', [NotificationTemplateController::class, 'index'])->name('templates.index');
        Route::put('/templates/{template}', [NotificationTemplateController::class, 'update'])->name('templates.update');

        // Billing / subscriptions (owner-only)
        Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
        Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    });

    // Command Center action endpoints
    Route::prefix('command-center')->name('command-center.')->group(function () {
        Route::post('/customers', [CommandCenterController::class, 'storeCustomer'])->name('store-customer');
        Route::post('/receivables', [CommandCenterController::class, 'storeReceivable'])->name('store-receivable');
        Route::post('/tasks', [CommandCenterController::class, 'storeTask'])->name('store-task');
        Route::put('/receivables/{receivable}', [CommandCenterController::class, 'updateReceivable'])->name('update-receivable');
        Route::post('/receivables/{receivable}/mark-paid', [CommandCenterController::class, 'markReceivablePaid'])->name('mark-paid');
        Route::post('/receivables/{receivable}/notify', [CommandCenterController::class, 'sendReceivableNotification'])->name('notify-receivable');
        Route::post('/run-notifications', [CommandCenterController::class, 'runDueNotifications'])->name('run-notifications');
        Route::put('/settings', [CommandCenterController::class, 'updateSettings'])->name('update-settings');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

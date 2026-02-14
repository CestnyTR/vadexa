<?php

use App\Http\Controllers\CommandCenterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MailLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\SmsLogController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Resource routes (CRUD pages still exist, just not in nav)
    Route::resource('customers', CustomerController::class)->except('show');
    Route::resource('receivables', ReceivableController::class)->except('show');
    Route::resource('tasks', TaskController::class)->except('show');

    // Log pages
    Route::get('/sms-logs', [SmsLogController::class, 'index'])->name('sms-logs.index');
    Route::get('/mail-logs', [MailLogController::class, 'index'])->name('mail-logs.index');

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

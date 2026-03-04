<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TimeEntryController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/welcome', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

// HourLedger routes (no auth required - local app)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::post('/time-entries/clock-in', [TimeEntryController::class, 'clockIn'])->name('time-entries.clock-in');
Route::post('/time-entries/clock-out', [TimeEntryController::class, 'clockOut'])->name('time-entries.clock-out');
Route::get('/calendar', [TimeEntryController::class, 'calendar'])->name('calendar');
Route::post('/time-entries', [TimeEntryController::class, 'store'])->name('time-entries.store');
Route::put('/time-entries/{timeEntry}', [TimeEntryController::class, 'update'])->name('time-entries.update');
Route::delete('/time-entries/{timeEntry}', [TimeEntryController::class, 'destroy'])->name('time-entries.destroy');

Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

// Auth & settings routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Existing auth-required routes preserved here
});

require __DIR__.'/settings.php';

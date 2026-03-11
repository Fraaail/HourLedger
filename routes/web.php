<?php

use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TimeEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TimeEntryController::class, 'index'])->name('dashboard');
Route::post('/time-in', [TimeEntryController::class, 'timeIn'])->name('time.in');
Route::post('/time-out', [TimeEntryController::class, 'timeOut'])->name('time.out');
Route::get('/calendar', [TimeEntryController::class, 'calendar'])->name('calendar');
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::post('/settings/timezone', [SettingsController::class, 'updateTimezone'])->name('settings.timezone');
Route::post('/journal/{date}', [\App\Http\Controllers\JournalController::class, 'store'])->name('journal.store');

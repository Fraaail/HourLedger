<?php

use App\Http\Controllers\JournalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TimeEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TimeEntryController::class, 'index'])->name('dashboard');
Route::post('/clock-in', [TimeEntryController::class, 'timeIn'])->name('time.in');
Route::post('/clock-out', [TimeEntryController::class, 'timeOut'])->name('time.out');
Route::get('/calendar', [TimeEntryController::class, 'calendar'])->name('calendar');
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::post('/settings/timezone', [SettingsController::class, 'updateTimezone'])->name('settings.timezone');
Route::post('/settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme');
Route::post('/journal/{date}', [JournalController::class, 'store'])->name('journal.store');
Route::post('/profiles', [ProfileController::class, 'store'])->name('profiles.store');
Route::post('/profiles/switch', [ProfileController::class, 'switchProfile'])->name('profiles.switch');

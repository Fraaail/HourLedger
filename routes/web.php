<?php

use App\Http\Controllers\JournalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TimeEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TimeEntryController::class, 'index'])->name('dashboard');
Route::post('/clock-in', [TimeEntryController::class, 'timeIn'])->name('time.in');
Route::post('/clock-out', [TimeEntryController::class, 'timeOut'])->name('time.out');
Route::get('/shortcut/clock-in', [TimeEntryController::class, 'timeIn'])->name('shortcut.time.in');
Route::get('/shortcut/clock-out', [TimeEntryController::class, 'timeOut'])->name('shortcut.time.out');
Route::get('/calendar', [TimeEntryController::class, 'calendar'])->name('calendar');
Route::get('/export/timesheet.csv', [TimeEntryController::class, 'exportTimesheetCsv'])->name('export.timesheet.csv');
Route::get('/profiles', [ProfileController::class, 'index'])->name('profiles.index');
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::post('/settings/timezone', [SettingsController::class, 'updateTimezone'])->name('settings.timezone');
Route::post('/settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme');
Route::post('/settings/missing-entries-reminder', [SettingsController::class, 'updateMissingEntriesReminder'])->name('settings.missing_entries_reminder');
Route::post('/settings/critical-alerts', [SettingsController::class, 'updateCriticalAlerts'])->name('settings.critical_alerts');
Route::get('/native/widget/summary', [TimeEntryController::class, 'widgetSummary'])->name('native.widget.summary');
Route::post('/journal/{date}', [JournalController::class, 'store'])->name('journal.store');
Route::post('/profiles', [ProfileController::class, 'store'])->name('profiles.store');
Route::post('/profiles/switch', [ProfileController::class, 'switchProfile'])->name('profiles.switch');
Route::patch('/profiles/{profile}', [ProfileController::class, 'update'])->name('profiles.update');
Route::post('/profiles/{profile}/archive', [ProfileController::class, 'archive'])->name('profiles.archive');
Route::post('/profiles/{profile}/unarchive', [ProfileController::class, 'unarchive'])->name('profiles.unarchive');
Route::delete('/profiles/{profile}', [ProfileController::class, 'destroy'])->name('profiles.destroy');

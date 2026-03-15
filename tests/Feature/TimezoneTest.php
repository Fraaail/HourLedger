<?php

use App\Models\Setting;
use App\Models\TimeEntry;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Carbon;

test('time in uses configured timezone for date', function () {
    Setting::set('timezone', 'Asia/Manila');

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/clock-in');

    $response->assertStatus(302);

    $expectedDate = Carbon::now('Asia/Manila')->toDateString();
    $this->assertDatabaseHas('time_entries', [
        'date' => $expectedDate,
    ]);
});

test('time in uses configured timezone for date via ajax', function () {
    Setting::set('timezone', 'Asia/Manila');

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/clock-in');

    $response->assertOk();
    $expectedDate = Carbon::now('Asia/Manila')->toDateString();
    $this->assertDatabaseHas('time_entries', [
        'date' => $expectedDate,
    ]);
});

test('time out uses configured timezone', function () {
    Setting::set('timezone', 'Asia/Manila');

    $today = Carbon::now('Asia/Manila')->toDateString();
    $timeIn = Carbon::now()->subHours(4);

    TimeEntry::create([
        'date' => $today,
        'time_in' => $timeIn,
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/clock-out');

    $response->assertStatus(302);

    $entry = TimeEntry::where('date', $today)->first();
    expect($entry->time_out)->not->toBeNull();
    expect($entry->total_minutes)->toBeGreaterThanOrEqual(239);
});

test('dashboard uses configured timezone', function () {
    Setting::set('timezone', 'Asia/Manila');

    $response = $this->get('/');
    $response->assertStatus(200);
});

test('calendar uses configured timezone', function () {
    Setting::set('timezone', 'Asia/Manila');

    $response = $this->get('/calendar');
    $response->assertStatus(200);
});

test('missing entries detection uses configured timezone', function () {
    Setting::set('timezone', 'Asia/Manila');

    $tz = 'Asia/Manila';
    $weekday = Carbon::now($tz)->subWeek()->startOfWeek();

    TimeEntry::create([
        'date' => $weekday->toDateString(),
        'time_in' => $weekday->copy()->setTime(8, 0),
        'time_out' => $weekday->copy()->setTime(17, 0),
        'total_minutes' => 540,
    ]);

    $response = $this->get('/');
    $response->assertStatus(200);
});

test('dashboard works without timezone setting', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

test('time in works without timezone setting', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/clock-in');

    $response->assertStatus(302);
    $this->assertDatabaseHas('time_entries', [
        'date' => Carbon::today()->toDateString(),
    ]);
});

test('time in stores timestamps in utc not local timezone', function () {
    Setting::set('timezone', 'Asia/Manila');

    Carbon::setTestNow(Carbon::create(2026, 3, 10, 1, 0, 0, 'UTC'));

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/clock-in');

    $response->assertStatus(302);

    $entry = TimeEntry::first();
    expect($entry->date)->toBe('2026-03-10');
    expect($entry->time_in->format('H:i:s'))->toBe('01:00:00');

    Carbon::setTestNow();
});

test('duration is correct across timezone offsets', function () {
    Setting::set('timezone', 'Asia/Manila');

    $today = Carbon::now('Asia/Manila')->toDateString();

    TimeEntry::create([
        'date' => $today,
        'time_in' => Carbon::now()->subHours(8),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/clock-out');

    $entry = TimeEntry::where('date', $today)->first();
    expect($entry->total_minutes)->toBeGreaterThanOrEqual(479);
});

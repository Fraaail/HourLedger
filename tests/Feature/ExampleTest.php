<?php

use App\Models\TimeEntry;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Carbon;

test('dashboard loads correctly', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

test('dashboard includes confirmation modal', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('id="confirmModal"', false);
    $response->assertSee('Clock In', false);
    $response->assertSee('Clock Out', false);
});

test('dashboard includes haptic feedback hooks for clock actions', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('clock_in_success', false);
    $response->assertSee('clock_out_completion', false);
    $response->assertSee('window.triggerHapticFeedback', false);
});

test('dashboard confirmation modal includes back-dismiss hooks', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('data-back-close-handler="closeModal"', false);
    $response->assertSee("window.pushModalHistory('confirmModal')", false);
    $response->assertSee("window.popModalHistory('confirmModal')", false);
});

test('dashboard includes iOS pull-to-refresh hooks', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('id="pullToRefreshIndicator"', false);
    $response->assertSee('Pull to refresh', false);
    $response->assertSee('registerPullToRefresh', false);
    $response->assertSee('isIOSPullSupported', false);
});

test('user can log time in', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/clock-in');

    $response->assertStatus(302);
    $this->assertDatabaseHas('time_entries', [
        'date' => Carbon::today()->toDateString(),
    ]);
});

test('user can log time out', function () {
    $today = Carbon::today()->toDateString();
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

test('calendar page loads correctly', function () {
    $response = $this->get('/calendar');
    $response->assertStatus(200);
});

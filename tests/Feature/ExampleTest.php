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

test('user can log time in', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/clock-in');

    $response->assertStatus(302);
    $this->assertDatabaseHas('time_entries', [
        'date' => Carbon::today()->toDateString(),
    ]);
});

test('user can log time in via ajax', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/clock-in');

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Clocked in successfully.',
        'redirect' => route('dashboard', [], false),
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

test('user can log time out via ajax', function () {
    $today = Carbon::today()->toDateString();

    TimeEntry::create([
        'date' => $today,
        'time_in' => Carbon::now()->subHours(2),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/clock-out');

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Clocked out successfully.',
        'redirect' => route('dashboard', [], false),
    ]);
});

test('calendar page loads correctly', function () {
    $response = $this->get('/calendar');
    $response->assertStatus(200);
});

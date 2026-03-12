<?php

use App\Models\TimeEntry;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Carbon;

test('dashboard loads correctly', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

test('dashboard includes confirmation dialog script', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('confirm(message)', false);
    $response->assertSee('Are you sure you want to Clock In?', false);
    $response->assertSee('Are you sure you want to Clock Out?', false);
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

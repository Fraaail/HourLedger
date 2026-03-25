<?php

use App\Models\TimeEntry;
use App\Support\ActiveProfile;
use Illuminate\Support\Carbon;

test('shortcut clock in successfully logs time', function () {
    $profileId = ActiveProfile::id();
    $response = $this->get('/shortcut/clock-in');

    $response->assertStatus(302);
    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('time_entries', [
        'profile_id' => $profileId,
        'date' => Carbon::today()->toDateString(),
    ]);
});

test('shortcut clock out successfully logs time out', function () {
    $profileId = ActiveProfile::id();
    $today = Carbon::today()->toDateString();
    $timeIn = Carbon::now()->subHours(4);

    TimeEntry::create([
        'profile_id' => $profileId,
        'date' => $today,
        'time_in' => $timeIn,
    ]);

    $response = $this->get('/shortcut/clock-out');
    $response->assertStatus(302);
    $response->assertRedirect(route('dashboard'));

    $entry = TimeEntry::where('profile_id', $profileId)->where('date', $today)->first();
    expect($entry->time_out)->not->toBeNull();
    expect($entry->total_minutes)->toBeGreaterThanOrEqual(239);
});

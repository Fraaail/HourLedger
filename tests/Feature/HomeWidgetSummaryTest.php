<?php

use App\Models\Profile;
use App\Models\TimeEntry;
use App\Support\ActiveProfile;
use Illuminate\Support\Carbon;

test('widget summary returns active profile totals and status', function () {
    $profile = ActiveProfile::current();
    $today = Carbon::today()->toDateString();

    TimeEntry::create([
        'profile_id' => $profile->id,
        'date' => $today,
        'time_in' => Carbon::now()->subHours(2),
    ]);

    $response = $this->getJson('/native/widget/summary');

    $response->assertOk()->assertJson([
        'profile_id' => $profile->id,
        'status' => 'clocked_in',
        'status_label' => 'Clocked In',
        'total_days' => 0,
    ]);
});

test('widget summary accepts explicit profile id', function () {
    $profile = Profile::create([
        'name' => 'Widget Profile',
        'is_default' => false,
        'is_archived' => false,
    ]);

    TimeEntry::create([
        'profile_id' => $profile->id,
        'date' => Carbon::today()->toDateString(),
        'time_in' => Carbon::now()->subHours(8),
        'time_out' => Carbon::now(),
        'total_minutes' => 480,
    ]);

    $response = $this->getJson('/native/widget/summary?profile_id='.$profile->id);

    $response->assertOk()->assertJson([
        'profile_id' => $profile->id,
        'status' => 'clocked_out',
        'status_label' => 'Clocked Out',
        'total_minutes' => 480,
        'total_days' => 1,
    ]);
});

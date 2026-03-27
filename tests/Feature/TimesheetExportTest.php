<?php

use App\Models\Journal;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\TimeEntry;
use Carbon\Carbon;

test('timesheet export returns csv for active profile only', function () {
    $profileA = Profile::create(['name' => 'Profile A']);
    $profileB = Profile::create(['name' => 'Profile B']);

    TimeEntry::create([
        'profile_id' => $profileA->id,
        'date' => '2026-03-01',
        'time_in' => Carbon::parse('2026-03-01 08:00:00', 'UTC'),
        'time_out' => Carbon::parse('2026-03-01 17:00:00', 'UTC'),
        'total_minutes' => 540,
    ]);

    Journal::create([
        'profile_id' => $profileA->id,
        'date' => '2026-03-01',
        'content' => 'Worked on API docs.',
    ]);

    TimeEntry::create([
        'profile_id' => $profileB->id,
        'date' => '2026-03-02',
        'time_in' => Carbon::parse('2026-03-02 08:00:00', 'UTC'),
        'time_out' => Carbon::parse('2026-03-02 17:00:00', 'UTC'),
        'total_minutes' => 540,
    ]);

    $response = $this->withSession(['active_profile_id' => $profileA->id])
        ->get('/export/timesheet.csv');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('Content-Disposition'))->toContain('attachment; filename="timesheet-');

    $csv = $response->getContent();

    expect($csv)->toContain('date,time_in,time_out,total_hours,journal');
    expect($csv)->toContain('2026-03-01');
    expect($csv)->toContain('Worked on API docs.');
    expect($csv)->toContain('9.00');
    expect($csv)->not->toContain('2026-03-02');
});

test('timesheet export uses profile timezone for displayed times', function () {
    $profile = Profile::create(['name' => 'Timezone Export']);

    TimeEntry::create([
        'profile_id' => $profile->id,
        'date' => '2026-03-03',
        'time_in' => Carbon::parse('2026-03-03 00:30:00', 'UTC'),
        'time_out' => Carbon::parse('2026-03-03 09:00:00', 'UTC'),
        'total_minutes' => 510,
    ]);

    Setting::create([
        'profile_id' => $profile->id,
        'key' => 'timezone',
        'value' => 'Asia/Manila',
    ]);

    $response = $this->withSession(['active_profile_id' => $profile->id])
        ->get('/export/timesheet.csv');

    $response->assertOk();

    $csv = $response->getContent();

    expect($csv)->toContain('2026-03-03 08:30:00');
    expect($csv)->toContain('2026-03-03 17:00:00');
});

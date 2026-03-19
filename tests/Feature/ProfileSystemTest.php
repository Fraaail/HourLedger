<?php

use App\Models\AttendanceLog;
use App\Models\Journal;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\TimeEntry;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

test('clock-in records are isolated by active profile', function () {
    $defaultProfile = Profile::where('is_default', true)->firstOrFail();
    $secondProfile = Profile::create(['name' => 'Teammate']);

    $this->withSession(['active_profile_id' => $defaultProfile->id])
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/clock-in')
        ->assertStatus(302);

    $this->withSession(['active_profile_id' => $secondProfile->id])
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/clock-in')
        ->assertStatus(302);

    $today = now()->toDateString();

    $this->assertDatabaseHas('time_entries', [
        'profile_id' => $defaultProfile->id,
        'date' => $today,
    ]);

    $this->assertDatabaseHas('time_entries', [
        'profile_id' => $secondProfile->id,
        'date' => $today,
    ]);
});

test('calendar only shows journals for the active profile', function () {
    $defaultProfile = Profile::where('is_default', true)->firstOrFail();
    $secondProfile = Profile::create(['name' => 'Intern 2']);
    $today = now()->toDateString();

    Journal::create([
        'profile_id' => $defaultProfile->id,
        'date' => $today,
        'content' => 'Default profile notes',
    ]);

    Journal::create([
        'profile_id' => $secondProfile->id,
        'date' => $today,
        'content' => 'Second profile notes',
    ]);

    $this->withSession(['active_profile_id' => $defaultProfile->id])
        ->get('/calendar')
        ->assertOk()
        ->assertSee('Default profile notes', false)
        ->assertDontSee('Second profile notes', false);

    $this->withSession(['active_profile_id' => $secondProfile->id])
        ->get('/calendar')
        ->assertOk()
        ->assertSee('Second profile notes', false)
        ->assertDontSee('Default profile notes', false);
});

test('settings are stored per profile', function () {
    $defaultProfile = Profile::where('is_default', true)->firstOrFail();
    $secondProfile = Profile::create(['name' => 'Intern 3']);

    $this->withSession(['active_profile_id' => $defaultProfile->id])
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/settings/timezone', ['timezone' => 'Asia/Manila'])
        ->assertStatus(302);

    $this->withSession(['active_profile_id' => $secondProfile->id])
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/settings/timezone', ['timezone' => 'Europe/London'])
        ->assertStatus(302);

    $this->assertDatabaseHas('settings', [
        'profile_id' => $defaultProfile->id,
        'key' => 'timezone',
        'value' => 'Asia/Manila',
    ]);

    $this->assertDatabaseHas('settings', [
        'profile_id' => $secondProfile->id,
        'key' => 'timezone',
        'value' => 'Europe/London',
    ]);
});

test('profile can be renamed', function () {
    $profile = Profile::create(['name' => 'Draft Name']);

    $this->withoutMiddleware(ValidateCsrfToken::class)
        ->patchJson('/profiles/'.$profile->id, ['name' => 'Renamed Profile'])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Profile updated.',
        ]);

    $this->assertDatabaseHas('profiles', [
        'id' => $profile->id,
        'name' => 'Renamed Profile',
    ]);
});

test('active profile can be archived and falls back to default profile', function () {
    $defaultProfile = Profile::where('is_default', true)->firstOrFail();
    $archivable = Profile::create(['name' => 'Temporary User']);

    $this->withSession(['active_profile_id' => $archivable->id])
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/profiles/'.$archivable->id.'/archive')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Profile archived.',
        ]);

    $this->assertDatabaseHas('profiles', [
        'id' => $archivable->id,
        'is_archived' => true,
    ]);

    $this->withSession(['active_profile_id' => $archivable->id])
        ->get('/')
        ->assertOk()
        ->assertSee($defaultProfile->name)
        ->assertDontSee($archivable->name);
});

test('default profile cannot be archived', function () {
    $defaultProfile = Profile::where('is_default', true)->firstOrFail();

    $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/profiles/'.$defaultProfile->id.'/archive')
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'The default profile cannot be archived.',
        ]);
});

test('archived profile can be unarchived', function () {
    $profile = Profile::create([
        'name' => 'Archived Candidate',
        'is_archived' => true,
    ]);

    $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/profiles/'.$profile->id.'/unarchive')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Profile unarchived.',
            'profile_id' => $profile->id,
        ]);

    $this->assertDatabaseHas('profiles', [
        'id' => $profile->id,
        'is_archived' => false,
    ]);
});

test('empty non-active profile can be deleted', function () {
    $defaultProfile = Profile::where('is_default', true)->firstOrFail();
    $profile = Profile::create(['name' => 'Delete Me']);

    $this->withSession(['active_profile_id' => $defaultProfile->id])
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->deleteJson('/profiles/'.$profile->id)
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Profile deleted.',
        ]);

    $this->assertDatabaseMissing('profiles', [
        'id' => $profile->id,
    ]);
});

test('profile with data can be deleted and removes its scoped records', function () {
    $defaultProfile = Profile::where('is_default', true)->firstOrFail();
    $profile = Profile::create(['name' => 'Has Data']);
    $today = now()->toDateString();

    TimeEntry::create([
        'profile_id' => $profile->id,
        'date' => $today,
        'time_in' => now(),
    ]);

    Journal::create([
        'profile_id' => $profile->id,
        'date' => $today,
        'content' => 'Temporary journal entry',
    ]);

    Setting::create([
        'profile_id' => $profile->id,
        'key' => 'theme',
        'value' => 'dark',
    ]);

    AttendanceLog::create([
        'profile_id' => $profile->id,
        'date' => $today,
        'time_in' => now(),
    ]);

    $this->withSession(['active_profile_id' => $defaultProfile->id])
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->deleteJson('/profiles/'.$profile->id)
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Profile deleted.',
        ]);

    $this->assertDatabaseMissing('profiles', [
        'id' => $profile->id,
    ]);

    $this->assertDatabaseMissing('time_entries', [
        'profile_id' => $profile->id,
        'date' => $today,
    ]);

    $this->assertDatabaseMissing('journals', [
        'profile_id' => $profile->id,
        'date' => $today,
    ]);

    $this->assertDatabaseMissing('settings', [
        'profile_id' => $profile->id,
        'key' => 'theme',
    ]);

    $this->assertDatabaseMissing('attendance_logs', [
        'profile_id' => $profile->id,
        'date' => $today,
    ]);
});

<?php

use App\Models\Journal;
use App\Models\Profile;
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

<?php

use App\Models\Setting;
use App\Models\TimeEntry;
use App\Support\ActiveProfile;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Carbon;

test('settings page loads correctly', function () {
    $response = $this->get('/settings');
    $response->assertStatus(200);
    $response->assertSee('Timezone', false);
    $response->assertSee('Theme', false);
    $response->assertDontSee('New Profile Name', false);
});

test('settings page displays timezone selector', function () {
    $response = $this->get('/settings');
    $response->assertStatus(200);
    $response->assertSee('Asia/Manila', false);
    $response->assertSee('America/New_York', false);
    $response->assertSee('settings-select', false);
});

test('user can update timezone', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/settings/timezone', ['timezone' => 'Asia/Manila']);

    $response->assertStatus(302);
    $response->assertRedirect(route('settings'));

    $this->assertDatabaseHas('settings', [
        'key' => 'timezone',
        'value' => 'Asia/Manila',
    ]);
});

test('timezone update rejects invalid timezone', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/settings/timezone', ['timezone' => 'Invalid/Zone']);

    $response->assertSessionHasErrors('timezone');
});

test('timezone update rejects empty timezone', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/settings/timezone', ['timezone' => '']);

    $response->assertSessionHasErrors('timezone');
});

test('settings page shows current timezone', function () {
    Setting::set('timezone', 'Asia/Manila');

    $response = $this->get('/settings');
    $response->assertStatus(200);
    $response->assertSee('Asia/Manila', false);
});

test('setting model can get and set values', function () {
    Setting::set('timezone', 'Europe/London');
    expect(Setting::get('timezone'))->toBe('Europe/London');

    Setting::set('timezone', 'Asia/Tokyo');
    expect(Setting::get('timezone'))->toBe('Asia/Tokyo');
});

test('setting model returns default when key missing', function () {
    expect(Setting::get('nonexistent', 'fallback'))->toBe('fallback');
    expect(Setting::get('nonexistent'))->toBeNull();
});

test('settings page has proper layout structure', function () {
    $response = $this->get('/settings');
    $response->assertStatus(200);
    $response->assertSee('app-main', false);
    $response->assertSee('bottom-nav', false);
});

test('settings nav link appears in layout', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('Settings', false);
    $response->assertSee(route('settings', [], false), false);
    $response->assertSee('Profile', false);
    $response->assertSee(route('profiles.index', [], false), false);
});

test('user can update theme', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/settings/theme', ['theme' => 'light']);

    $response->assertStatus(302);
    $response->assertRedirect(route('settings'));

    $this->assertDatabaseHas('settings', [
        'key' => 'theme',
        'value' => 'light',
    ]);
});

test('user can update timezone via ajax', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/settings/timezone', ['timezone' => 'Asia/Manila']);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Timezone updated.',
        'timezone' => 'Asia/Manila',
    ]);
    $response->assertJsonStructure(['currentTime']);

    $this->assertDatabaseHas('settings', [
        'key' => 'timezone',
        'value' => 'Asia/Manila',
    ]);
});

test('user can update theme via ajax', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/settings/theme', ['theme' => 'light']);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Theme updated.',
    ]);

    $this->assertDatabaseHas('settings', [
        'key' => 'theme',
        'value' => 'light',
    ]);
});

test('theme update rejects invalid theme', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/settings/theme', ['theme' => 'invalid-theme']);

    $response->assertSessionHasErrors('theme');
});

test('settings page shows selected theme', function () {
    Setting::set('theme', 'dark');

    $response = $this->get('/settings');
    $response->assertStatus(200);
    $response->assertSee('value="dark" selected', false);
});

test('settings page does not have manual save buttons', function () {
    $response = $this->get('/settings');
    $response->assertStatus(200);
    $response->assertDontSee('Save Theme', false);
    $response->assertDontSee('Save Timezone', false);
});

test('settings page does not include profile confirmation overlay', function () {
    $response = $this->get('/settings');
    $response->assertStatus(200);
    $response->assertDontSee('id="profileConfirmOverlay"', false);
    $response->assertDontSee('profile-confirmation-modal', false);
});

test('settings page shows missing entries reminder toggle', function () {
    $response = $this->get('/settings');
    $response->assertStatus(200);
    $response->assertSee('Missing Entry Reminders', false);
    $response->assertSee('missing_entries_reminder_enabled', false);
});

test('user can update missing entries reminder via ajax', function () {
    Carbon::setTestNow(Carbon::create(2026, 3, 26, 7, 30, 0, 'UTC'));

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/settings/missing-entries-reminder', ['enabled' => true]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Missing entry reminders updated.',
        'enabled' => true,
    ]);
    $response->assertJsonPath('payload.enabled', true);
    $response->assertJsonPath('payload.hour', 9);
    $response->assertJsonPath('payload.minute', 0);
    $response->assertJsonPath('payload.profile_name', ActiveProfile::current()->name);

    $this->assertDatabaseHas('settings', [
        'profile_id' => ActiveProfile::id(),
        'key' => 'missing_entries_reminder_enabled',
        'value' => '1',
    ]);

    Carbon::setTestNow();
});

test('reminder payload skips today when already clocked in', function () {
    Carbon::setTestNow(Carbon::create(2026, 3, 26, 1, 0, 0, 'UTC'));
    Setting::set('timezone', 'Asia/Manila');

    TimeEntry::create([
        'profile_id' => ActiveProfile::id(),
        'date' => Carbon::now('Asia/Manila')->toDateString(),
        'time_in' => Carbon::now(),
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/settings/missing-entries-reminder', ['enabled' => true]);

    $response->assertStatus(200);
    $response->assertJsonPath('payload.timezone', 'Asia/Manila');
    $response->assertJsonPath('payload.skip_today', true);

    Carbon::setTestNow();
});

test('user can disable missing entries reminder via ajax', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/settings/missing-entries-reminder', ['enabled' => false]);

    $response->assertStatus(200);
    $response->assertJsonPath('enabled', false);
    $response->assertJsonPath('payload.enabled', false);

    $this->assertDatabaseHas('settings', [
        'profile_id' => ActiveProfile::id(),
        'key' => 'missing_entries_reminder_enabled',
        'value' => '0',
    ]);
});

test('missing entries reminder update rejects invalid enabled value', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/settings/missing-entries-reminder', ['enabled' => 'invalid']);

    $response->assertSessionHasErrors('enabled');
});

test('settings page shows under-hours alert controls', function () {
    $response = $this->get('/settings');
    $response->assertStatus(200);
    $response->assertSee('End-of-Day Under-Hours Alerts', false);
    $response->assertSee('critical_alerts_enabled', false);
    $response->assertSee('critical_alert_required_hours', false);
    $response->assertSee('critical_alert_time', false);
});

test('user can update under-hours alerts via ajax', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/settings/critical-alerts', [
            'enabled' => true,
            'required_minutes' => 420,
            'hour' => 18,
            'minute' => 15,
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Under-hours alerts updated.',
        'enabled' => true,
    ]);
    $response->assertJsonPath('payload.required_minutes', 420);
    $response->assertJsonPath('payload.hour', 18);
    $response->assertJsonPath('payload.minute', 15);
    $response->assertJsonPath('payload.under_hours', true);

    $this->assertDatabaseHas('settings', [
        'profile_id' => ActiveProfile::id(),
        'key' => 'critical_alerts_enabled',
        'value' => '1',
    ]);
    $this->assertDatabaseHas('settings', [
        'profile_id' => ActiveProfile::id(),
        'key' => 'critical_alert_required_minutes',
        'value' => '420',
    ]);
});

test('under-hours payload marks target as met when enough minutes are logged', function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 3, 10, 0, 0, 'UTC'));
    Setting::set('timezone', 'UTC');

    TimeEntry::create([
        'profile_id' => ActiveProfile::id(),
        'date' => Carbon::now('UTC')->toDateString(),
        'time_in' => Carbon::now()->subHours(9),
        'time_out' => Carbon::now(),
        'total_minutes' => 540,
    ]);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/settings/critical-alerts', [
            'enabled' => true,
            'required_minutes' => 480,
            'hour' => 18,
            'minute' => 0,
        ]);

    $response->assertStatus(200);
    $response->assertJsonPath('payload.today_total_minutes', 540);
    $response->assertJsonPath('payload.under_hours', false);

    Carbon::setTestNow();
});

test('under-hours alert update rejects invalid required minutes', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post('/settings/critical-alerts', [
            'enabled' => true,
            'required_minutes' => 30,
            'hour' => 18,
            'minute' => 0,
        ]);

    $response->assertSessionHasErrors('required_minutes');
});

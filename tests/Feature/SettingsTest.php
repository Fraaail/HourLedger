<?php

use App\Models\Setting;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

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

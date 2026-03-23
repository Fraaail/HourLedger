<?php

use App\Models\Profile;
use App\Support\ActiveProfile;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Native\Mobile\Facades\Biometrics;
use Native\Mobile\Facades\System;

test('switching to a profile with biometric auth on mobile requires validation', function () {
    $profile = Profile::create([
        'name' => 'Secure Profile',
        'biometric_auth' => true,
    ]);

    System::shouldReceive('isMobile')->andReturn(true);
    Biometrics::shouldReceive('prompt->id->remember->prompt')->andReturn(true);

    $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/profiles/switch', ['profile_id' => $profile->id])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'requires_biometrics' => true,
            'profile_id' => $profile->id,
        ]);

    // Check that active profile is NOT changed yet
    $this->assertNotEquals($profile->id, session(ActiveProfile::SESSION_KEY));
});

test('switching to a profile with biometric auth after successful validation works', function () {
    $profile = Profile::create([
        'name' => 'Secure Profile',
        'biometric_auth' => true,
    ]);

    $biometricId = 'switch-profile-' . $profile->id;

    System::shouldReceive('isMobile')->andReturn(true);

    $this->withSession(['_native_biometric_success_' . $biometricId => true])
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/profiles/switch', ['profile_id' => $profile->id])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Profile switched.',
            'profile_id' => $profile->id,
        ]);

    $this->assertEquals($profile->id, session(ActiveProfile::SESSION_KEY));
});

test('switching to a profile with biometric auth on web does not require validation', function () {
    $profile = Profile::create([
        'name' => 'Secure Profile on Web',
        'biometric_auth' => true,
    ]);

    System::shouldReceive('isMobile')->andReturn(false);

    $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson('/profiles/switch', ['profile_id' => $profile->id])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Profile switched.',
            'profile_id' => $profile->id,
        ]);

    $this->assertEquals($profile->id, session(ActiveProfile::SESSION_KEY));
});

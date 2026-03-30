<?php

test('profiles page loads correctly', function () {
    $response = $this->get('/profiles');

    $response->assertOk();
    $response->assertSee('Profiles', false);
    $response->assertSee('New Profile Name', false);
    $response->assertSee('Create Profile', false);
    $response->assertSee('View All Profiles', false);
    $response->assertSee('profile-management-list hidden', false);
});

test('profiles page includes profile confirmation overlay', function () {
    $response = $this->get('/profiles');

    $response->assertOk();
    $response->assertSee('id="profileConfirmOverlay"', false);
    $response->assertSee('profile-confirmation-modal', false);
    $response->assertSee('Unarchive', false);
});

test('profile navigation item is active on profiles page', function () {
    $response = $this->get('/profiles');

    $response->assertOk();
    $response->assertSee('class="nav-item active"', false);
    $response->assertSee('>Profile<', false);
    $response->assertSee(route('profiles.index', [], false), false);
});

test('profiles page includes deletion warning haptic hook', function () {
    $response = $this->get('/profiles');

    $response->assertOk();
    $response->assertSee('deletion_warning', false);
    $response->assertSee("window.triggerHapticFeedback('deletion_warning')", false);
});

test('profiles confirmation modal includes back-dismiss hooks', function () {
    $response = $this->get('/profiles');

    $response->assertOk();
    $response->assertSee('data-back-close-handler="closeProfileConfirm"', false);
    $response->assertSee("window.pushModalHistory('profileConfirmOverlay')", false);
    $response->assertSee("window.popModalHistory('profileConfirmOverlay')", false);
});

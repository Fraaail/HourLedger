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

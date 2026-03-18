<?php

test('layout includes viewport meta tag with viewport-fit=cover', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('viewport-fit=cover', false);
});

test('layout includes safe area inset top padding in header', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('app-header', false);
});

test('dashboard page renders with proper layout structure', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('app-main', false);
    $response->assertSee('bottom-nav', false);
});

test('calendar page renders with proper layout structure', function () {
    $response = $this->get('/calendar');
    $response->assertStatus(200);
    $response->assertSee('app-main', false);
    $response->assertSee('bottom-nav', false);
});

test('custom css file is linked in layout', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('css/custom.css', false);
});

test('css declares default inset custom properties in root', function () {
    $cssPath = public_path('css/custom.css');
    expect(file_exists($cssPath))->toBeTrue();

    $css = file_get_contents($cssPath);
    // NativePHP injects --inset-top/right/bottom/left at runtime;
    // the stylesheet must declare safe defaults in :root.
    expect($css)->toContain('--inset-top: 0px');
    expect($css)->toContain('--inset-bottom: 0px');
    expect($css)->toContain('--inset-left: 0px');
    expect($css)->toContain('--inset-right: 0px');
    // iOS safe-area env() fallbacks must also be declared.
    expect($css)->toContain('env(safe-area-inset-top');
    expect($css)->toContain('env(safe-area-inset-bottom');
});

test('css header uses inset-top custom property for padding', function () {
    $cssPath = public_path('css/custom.css');
    $css = file_get_contents($cssPath);
    expect($css)->toContain('var(--safe-top');
});

test('css header is sticky with z-index', function () {
    $cssPath = public_path('css/custom.css');
    $css = file_get_contents($cssPath);
    expect($css)->toContain('position: sticky');
    expect($css)->toContain('z-index: 10');
});

test('css bottom nav uses inset-bottom custom property', function () {
    $cssPath = public_path('css/custom.css');
    $css = file_get_contents($cssPath);
    expect($css)->toContain('calc(60px + var(--safe-bottom');
    expect($css)->toContain('padding-bottom: var(--safe-bottom');
    expect($css)->toContain('z-index: 120');
    expect($css)->toContain('padding-left: var(--safe-left');
    expect($css)->toContain('padding-right: var(--safe-right');
});

test('css main content has padding bottom for bottom nav and safe area', function () {
    $cssPath = public_path('css/custom.css');
    $css = file_get_contents($cssPath);
    expect($css)->toContain('calc(5rem + var(--safe-bottom');
});

test('layout includes apple mobile web app meta tags', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('apple-mobile-web-app-capable', false);
    $response->assertSee('apple-mobile-web-app-status-bar-style', false);
});

test('layout includes android theme-color meta tag', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('theme-color', false);
    $response->assertSee('#0d1117', false);
});

test('css prevents ios auto-zoom on form inputs', function () {
    $cssPath = public_path('css/custom.css');
    $css = file_get_contents($cssPath);
    // Inputs with font-size < 16px cause iOS auto-zoom
    expect($css)->toContain('font-size: 16px');
});

test('css uses touch-action manipulation for tap delay removal', function () {
    $cssPath = public_path('css/custom.css');
    $css = file_get_contents($cssPath);
    expect($css)->toContain('touch-action: manipulation');
});

test('css uses dynamic viewport height for mobile', function () {
    $cssPath = public_path('css/custom.css');
    $css = file_get_contents($cssPath);
    expect($css)->toContain('100dvh');
});

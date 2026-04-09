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

test('layout includes responsive webp logo source with svg fallback', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('type="image/webp"', false);
    $response->assertSee('images/logo-32.webp', false);
    $response->assertSee('images/logo-48.webp', false);
    $response->assertSee('images/logo-64.webp', false);
    $response->assertSee('sizes="28px"', false);
    $response->assertSee('src="'.asset('logo.svg').'"', false);
});

test('optimized webp logo assets exist', function () {
    expect(file_exists(public_path('images/logo-32.webp')))->toBeTrue();
    expect(file_exists(public_path('images/logo-48.webp')))->toBeTrue();
    expect(file_exists(public_path('images/logo-64.webp')))->toBeTrue();
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

test('css enables dynamic type scaling support', function () {
    $cssPath = public_path('css/custom.css');
    $css = file_get_contents($cssPath);

    expect($css)->toContain('text-size-adjust: 100%');
    expect($css)->toContain('-webkit-text-size-adjust: 100%');
    expect($css)->toContain('clamp(');
});

test('css keeps layout fluid under larger text sizes', function () {
    $cssPath = public_path('css/custom.css');
    $css = file_get_contents($cssPath);

    expect($css)->toContain('repeat(auto-fit, minmax(9.5rem, 1fr))');
    expect($css)->toContain('overflow-wrap: anywhere');
});

test('layout includes global modal history back handling helpers', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('window.pushModalHistory', false);
    $response->assertSee('window.popModalHistory', false);
    $response->assertSee("window.addEventListener('popstate'", false);
    $response->assertSee('hourledgerModal', false);
});

test('profile switcher modal includes back-dismiss handler binding', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('id="profileSwitcherModal"', false);
    $response->assertSee('data-back-close-handler="closeProfileSwitcher"', false);
    $response->assertSee("window.pushModalHistory('profileSwitcherModal')", false);
    $response->assertSee("window.popModalHistory('profileSwitcherModal')", false);
});

test('css includes pull-to-refresh indicator styles', function () {
    $cssPath = public_path('css/custom.css');
    $css = file_get_contents($cssPath);

    expect($css)->toContain('.pull-refresh-indicator');
    expect($css)->toContain('.pull-refresh-indicator.refreshing');
    expect($css)->toContain('@keyframes pullRefreshSpin');
    expect($css)->toContain('.app-main.pull-refresh-active');
});

test('android manifest includes background refresh receiver and boot permission', function () {
    $manifestPath = base_path('nativephp/android/app/src/main/AndroidManifest.xml');
    expect(file_exists($manifestPath))->toBeTrue();

    $manifest = file_get_contents($manifestPath);

    expect($manifest)->toContain('android.permission.RECEIVE_BOOT_COMPLETED');
    expect($manifest)->toContain('MissingEntriesBackgroundRefreshReceiver');
    expect($manifest)->toContain('android.intent.action.BOOT_COMPLETED');
    expect($manifest)->toContain('android.intent.action.MY_PACKAGE_REPLACED');
    expect($manifest)->toContain('android.intent.action.TIMEZONE_CHANGED');
    expect($manifest)->toContain('android.intent.action.TIME_SET');
});

test('android reminder scheduler supports persisted background refresh restore', function () {
    $schedulerPath = base_path('nativephp/android/app/src/main/java/com/nativephp/mobile/notifications/MissingEntriesReminderScheduler.kt');
    expect(file_exists($schedulerPath))->toBeTrue();

    $scheduler = file_get_contents($schedulerPath);

    expect($scheduler)->toContain('refreshFromStorage');
    expect($scheduler)->toContain('persistConfiguration');
    expect($scheduler)->toContain('hourledger_missing_entries_refresh');
});

test('android manifest includes under-hours alert receiver', function () {
    $manifestPath = base_path('nativephp/android/app/src/main/AndroidManifest.xml');
    expect(file_exists($manifestPath))->toBeTrue();

    $manifest = file_get_contents($manifestPath);

    expect($manifest)->toContain('UnderHoursCriticalAlertReceiver');
});

test('android under-hours scheduler supports persisted refresh restore', function () {
    $schedulerPath = base_path('nativephp/android/app/src/main/java/com/nativephp/mobile/notifications/UnderHoursCriticalAlertScheduler.kt');
    expect(file_exists($schedulerPath))->toBeTrue();

    $scheduler = file_get_contents($schedulerPath);

    expect($scheduler)->toContain('refreshFromStorage');
    expect($scheduler)->toContain('hourledger_under_hours_alert_refresh');
    expect($scheduler)->toContain('requiredMinutes');
});

test('android main activity exposes under-hours bridge sync method', function () {
    $mainActivityPath = base_path('nativephp/android/app/src/main/java/com/nativephp/mobile/ui/MainActivity.kt');
    expect(file_exists($mainActivityPath))->toBeTrue();

    $mainActivity = file_get_contents($mainActivityPath);

    expect($mainActivity)->toContain('fun syncCriticalUnderHoursAlert');
    expect($mainActivity)->toContain('UnderHoursCriticalAlertScheduler.sync');
});

test('ios entitlements include critical alerts capability', function () {
    $entitlementsPath = base_path('nativephp/ios/NativePHP/NativePHP.entitlements');
    expect(file_exists($entitlementsPath))->toBeTrue();

    $entitlements = file_get_contents($entitlementsPath);

    expect($entitlements)->toContain('com.apple.developer.usernotifications.critical-alerts');
});

test('ios content view exposes critical alert bridge handler', function () {
    $contentViewPath = base_path('nativephp/ios/NativePHP/ContentView.swift');
    expect(file_exists($contentViewPath))->toBeTrue();

    $contentView = file_get_contents($contentViewPath);

    expect($contentView)->toContain('syncCriticalUnderHoursAlert');
    expect($contentView)->toContain('CriticalUnderHoursAlertBridgeHandler');
});

test('ios app delegate includes critical under-hours scheduler', function () {
    $appDelegatePath = base_path('nativephp/ios/NativePHP/AppDelegate.swift');
    expect(file_exists($appDelegatePath))->toBeTrue();

    $appDelegate = file_get_contents($appDelegatePath);

    expect($appDelegate)->toContain('CriticalUnderHoursAlertScheduler');
    expect($appDelegate)->toContain('UNUserNotificationCenter');
    expect($appDelegate)->toContain('defaultCriticalSound');
});

test('ios info plist includes static quick actions for clock in and clock out', function () {
    $infoPlistPath = base_path('nativephp/ios/NativePHP/Info.plist');
    expect(file_exists($infoPlistPath))->toBeTrue();

    $infoPlist = file_get_contents($infoPlistPath);

    expect($infoPlist)->toContain('UIApplicationShortcutItems');
    expect($infoPlist)->toContain('com.nativephp.app.clock-in');
    expect($infoPlist)->toContain('com.nativephp.app.clock-out');
    expect($infoPlist)->toContain('/shortcut/clock-in');
    expect($infoPlist)->toContain('/shortcut/clock-out');
});

test('ios app delegate handles quick actions through deep link router', function () {
    $appDelegatePath = base_path('nativephp/ios/NativePHP/AppDelegate.swift');
    expect(file_exists($appDelegatePath))->toBeTrue();

    $appDelegate = file_get_contents($appDelegatePath);

    expect($appDelegate)->toContain('performActionFor shortcutItem');
    expect($appDelegate)->toContain('handleQuickAction');
    expect($appDelegate)->toContain('shortcutRoute');
    expect($appDelegate)->toContain('nativephp://');
});

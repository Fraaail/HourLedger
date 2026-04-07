<?php

test('android manifest registers static shortcuts metadata', function () {
    $manifestPath = base_path('nativephp/android/app/src/main/AndroidManifest.xml');

    expect(file_exists($manifestPath))->toBeTrue();

    $manifest = file_get_contents($manifestPath);

    expect($manifest)->toContain('android.app.shortcuts');
    expect($manifest)->toContain('@xml/shortcuts');
});

test('android shortcuts xml contains clock in and clock out actions', function () {
    $shortcutsPath = base_path('nativephp/android/app/src/main/res/xml/shortcuts.xml');

    expect(file_exists($shortcutsPath))->toBeTrue();

    $shortcutsXml = file_get_contents($shortcutsPath);

    expect($shortcutsXml)->toContain('shortcutId="clock_in"');
    expect($shortcutsXml)->toContain('shortcutId="clock_out"');
    expect($shortcutsXml)->toContain('nativephp://shortcut/clock-in');
    expect($shortcutsXml)->toContain('nativephp://shortcut/clock-out');
});

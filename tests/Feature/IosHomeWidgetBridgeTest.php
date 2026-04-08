<?php

test('dashboard syncHomeWidget supports iOS message handler fallback', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('window.webkit.messageHandlers.syncHomeWidget', false);
});

test('ios content view registers syncHomeWidget bridge handler', function () {
    $contentViewPath = base_path('nativephp/ios/NativePHP/ContentView.swift');

    expect(file_exists($contentViewPath))->toBeTrue();

    $contentView = file_get_contents($contentViewPath);

    expect($contentView)->toContain('HomeWidgetBridgeHandler');
    expect($contentView)->toContain('contentController.add(context.coordinator.homeWidgetBridgeHandler, name: "syncHomeWidget")');
    expect($contentView)->toContain('window.AndroidBridge.syncHomeWidget');
});

test('ios home widget store uses app group and reloads timelines', function () {
    $homeWidgetStorePath = base_path('nativephp/ios/NativePHP/HomeWidgetStore.swift');

    expect(file_exists($homeWidgetStorePath))->toBeTrue();

    $storeSource = file_get_contents($homeWidgetStorePath);

    expect($storeSource)->toContain('group.com.nativephp.hourledger');
    expect($storeSource)->toContain('WidgetCenter.shared.reloadAllTimelines()');
    expect($storeSource)->toContain('hourledger.home_widget.snapshot');
});

test('ios entitlements include widget app group', function () {
    $entitlementsPath = base_path('nativephp/ios/NativePHP/NativePHP.entitlements');

    expect(file_exists($entitlementsPath))->toBeTrue();

    $entitlements = file_get_contents($entitlementsPath);

    expect($entitlements)->toContain('com.apple.security.application-groups');
    expect($entitlements)->toContain('group.com.nativephp.hourledger');
});

test('ios widgetkit scaffold files exist', function () {
    $widgetBundlePath = base_path('nativephp/ios/NativePHPWidgets/HourLedgerWidgetBundle.swift');
    $widgetFilePath = base_path('nativephp/ios/NativePHPWidgets/HourLedgerHomeWidget.swift');
    $widgetInfoPath = base_path('nativephp/ios/NativePHPWidgets/Info.plist');

    expect(file_exists($widgetBundlePath))->toBeTrue();
    expect(file_exists($widgetFilePath))->toBeTrue();
    expect(file_exists($widgetInfoPath))->toBeTrue();

    $widgetBundle = file_get_contents($widgetBundlePath);
    $widgetInfo = file_get_contents($widgetInfoPath);

    expect($widgetBundle)->toContain('WidgetBundle');
    expect($widgetInfo)->toContain('com.apple.widgetkit-extension');
});

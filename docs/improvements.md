# Mobile Platform Recommendations Checklist

This document outlines recommended features and optimizations to enhance the HourLedger experience on Android and iOS devices.

## 1. Native Integration & Security

- [x] **Biometric Authentication**
    - **Recommendation:** Integrate FaceID (iOS) and Fingerprint/Face Unlock (Android) for profile access.
    - **Benefit:** Provides a secure way for multiple users to protect their individual logs on a shared device without needing complex passwords.
    - **Implementation:** Utilize NativePHP's bridge to access device keychain and local authentication APIs.

- [x] **Home Screen Widgets (Android)**
    - **Recommendation:** Create small and medium widgets for the home screen.
    - **Benefit:** Allows users to see their "Total Hours" and "Current Status" (Clocked In/Out) without opening the app.
    - **Implementation:** Added Android `AppWidgetProvider` + RemoteViews (small/medium adaptive layouts), plus dashboard-to-native bridge sync and a Laravel widget summary endpoint for profile-scoped values. iOS widget implementation remains pending until iOS native target scaffolding is added.

- [x] **App Shortcuts / Quick Actions**
    - **Recommendation:** Implement "Quick Actions" (iOS) and "App Shortcuts" (Android) on the app icon.
    - **Benefit:** Enables users to "Clock In" or "Clock Out" directly from the home screen with a long-press.
    - **Implementation:** Android `shortcuts.xml` and Manifest meta-data added utilizing existing NativePHP deep link intent capture mechanisms. iOS Quick Actions implementation remains pending until iOS native target scaffolding is added.

## 2. Notifications & Engagement

- [x] **Push Notifications for Missing Entries**
    - **Recommendation:** Implement local notifications to alert users if they haven't clocked in by a certain time on weekdays.
    - **Benefit:** Improves data accuracy by reducing "forgotten" entries.
    - **Implementation:** Added Android local reminder scheduling through a native bridge (`syncMissingEntriesReminder`) with timezone-aware weekday alarms, a profile-scoped settings toggle, and runtime notification permission support. Current implementation re-schedules to the next weekday at 9:00 AM and updates schedule state from Dashboard/Settings interactions.

- [ ] **Critical Alerts (iOS)**
    - **Recommendation:** Use iOS Critical Alerts for end-of-day reminders if hours are significantly under the requirement.
    - **Benefit:** Ensures the user notices important tracking deadlines even when the phone is on mute.
    - **Implementation Note:** Blocked for now because this repository currently contains only the Android native target. iOS critical alerts require iOS project scaffolding, Apple entitlement approval, and APNs critical alert configuration.

## 3. Data Portability & Sharing

- [x] **Native Share Sheet Integration**
    - **Recommendation:** Add an "Export & Share" feature that generates a PDF or CSV timesheet.
    - **Benefit:** Allows users to easily send their logs to supervisors or school coordinators directly from the app.
    - **Implementation:** Added profile-scoped CSV export endpoint (`/export/timesheet.csv`) with timezone-adjusted timestamps and journal columns, plus Settings UI action that uses `navigator.share()` with file payload where supported and falls back to direct CSV download.

- [x] **Haptic Feedback**
    - **Recommendation:** Implement distinct haptic patterns for "Clock In" (success), "Clock Out" (completion), and "Deletion" (warning).
    - **Benefit:** Provides physical confirmation of actions, making the app feel more "native" and responsive.
    - **Implementation:** Added a global haptic helper in the base layout using `navigator.vibrate()` with distinct patterns (`clock_in_success`, `clock_out_completion`, `deletion_warning`) and wired it to dashboard clock action success flow and profile delete warning flow.

## 4. UI/UX Refinements

- [x] **Dynamic Type Support (iOS)**
    - **Recommendation:** Ensure all JetBrains Mono typography scales correctly with the system's "Larger Text" settings.
    - **Benefit:** Critical for accessibility and users with visual impairments.
    - **Implementation:** Updated typography and key layout text sizes to scalable `rem`/`clamp()` values, enabled `text-size-adjust`/`-webkit-text-size-adjust`, converted fixed text-adjacent pixel values to rem where appropriate, and added overflow-safe wrapping plus auto-fit metric-card columns to keep layout stable at larger text sizes.

- [x] **Android "Back" Gesture Handling**
    - **Recommendation:** Intercept the Android back gesture/button to handle modal dismissals before closing the app.
    - **Benefit:** Prevents accidental app exits when the user just wants to close a profile overlay or confirmation modal.
    - **Implementation:** Added a global modal history helper (`window.pushModalHistory` / `window.popModalHistory`) in the base layout and `popstate` interception so Android back closes visible overlays first (dashboard confirmation modal, profile confirmation modal, profile switcher sheet) before normal navigation/exit behavior.

- [x] **iOS "Pull to Refresh"**
    - **Recommendation:** Implement a custom pull-to-refresh on the Dashboard.
    - **Benefit:** A familiar gesture for mobile users to trigger a re-calculation of stats or sync check.
    - **Implementation:** Added a custom Dashboard pull-to-refresh gesture gated to iOS-style touch environments with a visual indicator, release-to-refresh threshold, modal-awareness checks, and a safe reload trigger for recalculating rendered stats.

## 5. Performance Improvements

- [ ] **Image Optimization**
    - **Recommendation:** Use WebP format for all assets and provide responsive image sizes.
    - **Benefit:** Reduces memory usage and improves loading speed within the WebView.

- [ ] **Background Refresh (Background App Refresh)**
    - **Recommendation:** Enable background fetch to calculate missing entries even when the app isn't active.
    - **Benefit:** Keeps notifications accurate without requiring the user to open the app daily.

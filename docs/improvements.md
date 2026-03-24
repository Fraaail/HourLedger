# Mobile Platform Recommendations Checklist

This document outlines recommended features and optimizations to enhance the HourLedger experience on Android and iOS devices.

## 1. Native Integration & Security

- [x] **Biometric Authentication**
    - **Recommendation:** Integrate FaceID (iOS) and Fingerprint/Face Unlock (Android) for profile access.
    - **Benefit:** Provides a secure way for multiple users to protect their individual logs on a shared device without needing complex passwords.
    - **Implementation:** Utilize NativePHP's bridge to access device keychain and local authentication APIs.

- [ ] **Home Screen Widgets**
    - **Recommendation:** Create small and medium widgets for the home screen.
    - **Benefit:** Allows users to see their "Total Hours" and "Current Status" (Clocked In/Out) without opening the app.
    - **Implementation:** Requires platform-specific widget code (SwiftUI for iOS, RemoteViews for Android) bridged via NativePHP.

- [ ] **App Shortcuts / Quick Actions**
    - **Recommendation:** Implement "Quick Actions" (iOS) and "App Shortcuts" (Android) on the app icon.
    - **Benefit:** Enables users to "Clock In" or "Clock Out" directly from the home screen with a long-press.
    - **Implementation:** Configure static or dynamic shortcuts in the native manifest/plist via the NativePHP wrapper.

## 2. Notifications & Engagement

- [ ] **Push Notifications for Missing Entries**
    - **Recommendation:** Implement local notifications to alert users if they haven't clocked in by a certain time on weekdays.
    - **Benefit:** Improves data accuracy by reducing "forgotten" entries.
    - **Implementation:** Schedule local notifications using NativePHP's notification API based on the user's timezone settings.

- [ ] **Critical Alerts (iOS)**
    - **Recommendation:** Use iOS Critical Alerts for end-of-day reminders if hours are significantly under the requirement.
    - **Benefit:** Ensures the user notices important tracking deadlines even when the phone is on mute.

## 3. Data Portability & Sharing

- [ ] **Native Share Sheet Integration**
    - **Recommendation:** Add an "Export & Share" feature that generates a PDF or CSV timesheet.
    - **Benefit:** Allows users to easily send their logs to supervisors or school coordinators directly from the app.
    - **Implementation:** Use Laravel's PDF generation (e.g., DomPDF) and trigger the native `navigator.share()` or NativePHP share dialog.

- [ ] **Haptic Feedback**
    - **Recommendation:** Implement distinct haptic patterns for "Clock In" (success), "Clock Out" (completion), and "Deletion" (warning).
    - **Benefit:** Provides physical confirmation of actions, making the app feel more "native" and responsive.
    - **Implementation:** Use the `vibrate` API or NativePHP haptic bridge.

## 4. UI/UX Refinements

- [ ] **Dynamic Type Support (iOS)**
    - **Recommendation:** Ensure all JetBrains Mono typography scales correctly with the system's "Larger Text" settings.
    - **Benefit:** Critical for accessibility and users with visual impairments.
    - **Implementation:** Use `rem` units and verify layout fluidness under extreme scaling.

- [ ] **Android "Back" Gesture Handling**
    - **Recommendation:** Intercept the Android back gesture/button to handle modal dismissals before closing the app.
    - **Benefit:** Prevents accidental app exits when the user just wants to close a profile overlay or confirmation modal.
    - **Implementation:** Use the `popstate` event or NativePHP's window management hooks.

- [ ] **iOS "Pull to Refresh"**
    - **Recommendation:** Implement a custom pull-to-refresh on the Dashboard.
    - **Benefit:** A familiar gesture for mobile users to trigger a re-calculation of stats or sync check.

## 5. Performance Improvements

- [ ] **Image Optimization**
    - **Recommendation:** Use WebP format for all assets and provide responsive image sizes.
    - **Benefit:** Reduces memory usage and improves loading speed within the WebView.

- [ ] **Background Refresh (Background App Refresh)**
    - **Recommendation:** Enable background fetch to calculate missing entries even when the app isn't active.
    - **Benefit:** Keeps notifications accurate without requiring the user to open the app daily.

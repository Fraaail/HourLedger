# Implementation Checklist

Based on the [Project Overview](project_overview.md), the following tasks need to be completed:

## Phase 1: Environment & Scaffolding
- [x] Initialize Laravel project and configure `.env` (SQLite connection, etc.).
- [x] Create SQLite database and run default migrations.
- [x] Create the `TimeEntry` model and `time_entries` migration table.
- [x] Set up JetBrains Mono font locally or via Google Fonts in a base layout.

## Phase 2: Backend Logic & Controllers
- [x] Implement `TimeEntryController` to handle dashboard stats.
- [x] Add `timeIn` and `timeOut` methods for recording timestamps.
- [x] Create a helper to calculate missing entry notifications (check business days without an entry).
- [x] Implement calendar data endpoint/method to retrieve rendering time by month.
- [x] Create the `Journal` model, migration, and `JournalController` to handle user journal entries.
- [x] Implement a profile context system so all time entries, journals, and settings are scoped to the active profile.
- [x] Add profile switching and profile creation endpoints (`/profiles/switch`, `/profiles`).

## Phase 3: Frontend Views & Design System
- [x] Create `layouts/app.blade.php` structure emphasizing a mobile-only viewport.
- [x] Write a reset and customized stylesheet (`resources/css/app.css`):
    - Set dark/professional color variables (clean, no weird gradients, no emojis).
    - Enforce JetBrains Mono font.
    - Setup mobile-friendly layout and touch targets for buttons.
- [x] Apply CSS transitions and keyframe animations for interactions (buttons, modals, views).
- [x] Handle mobile safe-area insets (status bar, navigation bar) via NativePHP-injected `--inset-*` CSS custom properties and iOS `env(safe-area-inset-*)` fallbacks.
- [x] Optimize for both Android and iOS: `theme-color`, `apple-mobile-web-app-capable`, `touch-action: manipulation`, dynamic viewport height (`dvh`), GPU-accelerated transforms, 48px minimum touch targets, and `font-size: 16px` on inputs to prevent iOS auto-zoom.
- [x] Rename `/time-out` route to `/clock-out` to avoid reserved routing conflict on iOS NativePHP/Jump.
- [x] Use relative routing (`route(..., [], false)`) for all navigation links and redirects.
- [x] Replace all native HTML form POST submissions with JavaScript `fetch()` to work around iOS WKWebView silently dropping `HTTPBody` on POST requests (known WebKit bug), which caused 404 errors on clock-in, clock-out, settings save, and journal save.

## Phase 4: Application Features
- [x] **Multi-Profile Mode:**
    - [x] Support multiple profiles on one device with independent data per profile.
    - [x] Add a header profile switcher available from all pages.
    - [x] Replace native select profile switcher with a professional custom bottom-sheet modal overlay.
    - [x] Add a dedicated Profile navigation panel.
    - [x] Add profile lifecycle management in Profile (create, rename, archive, unarchive, delete with safety checks, including scoped data cleanup on delete).
- [x] **Dashboard (Home View):**
    - [x] Display Total Rendered Time & Total Rendered Days dynamically.
    - [x] Show action button (Time In / Time Out) based on current state.
    - [x] Redesigned confirmation dialog for Time In / Time Out with custom modal to match system aesthetics.
    - [x] Display missing entries notification at the top of the screen if applicable.
- [x] **Calendar View:**
    - [x] Render a monthly grid layout using CSS grid.
    - [x] Highlight days uniquely if hours are rendered.
    - [x] Add touch interaction to reveal daily "Time In" and "Time Out" details.
    - [x] Integrate daily journal functionality in the tap-to-reveal details.
    - [x] Mark past days lacking a journal log with a red indicator.
- [x] **Settings:**
    - [x] Theme selection (Dark, Light, System) with instant preview and automatic asynchronous saving (no page reload).
    - [x] Timezone selection with automatic asynchronous saving and dynamic UI updates (no page reload).
    - [x] Timezone-aware timestamps throughout the app (dashboard, calendar, time in/out).
    - [x] All times stored in UTC; converted to user timezone for display only.
    - [x] Settings page accessible from bottom navigation.
    - [x] Keep bottom navigation always visible with safe-area handling and elevated z-index.
- [x] **Profile Panel:**
    - [x] Dedicated Profile panel accessible from bottom navigation.
    - [x] Custom overlay confirmations for profile create/edit/archive/unarchive/delete actions.
    - [x] Persistent **"View All Profiles"** toggle to hide the profile management list by default for a cleaner UI.
- [x] **Testing Transitions:** Ensure no glitchy or instant page loads; add subtle fading animations between Dashboard and Calendar.

## Phase 5: Build & Finalization
- [x] Ensure all assets are properly linked and optimized.
- [x] Simulate or test responsive dimensions.
- [x] Validate NativePHP integration (`php artisan native:serve` or `php artisan serve`).
- [x] Write feature tests for all core functionality (dashboard, time in/out, calendar, settings, timezone).
- [x] Create a `scripts/setup.php` script to automate `.env`, app key, database creation and migrations securely across operating systems.
- [x] Implement CI/CD pipeline for automated testing (GitHub Actions using PHP 8.4).
- [x] Verify tests pass successfully.
- [x] Run `pre-commit run --all-files` and fix any issues.
- [x] Run lint/style checks (`./vendor/bin/pint --test`) and resolve failures.

## Phase 6: Mobile Platform Optimization & Recommendations
- [x] Research and document platform-specific feature recommendations for Android and iOS (biometrics, widgets, notifications, etc.) in `docs/improvements.md`.
- [x] Link mobile recommendations to the main project documentation.
- [x] Implement Biometric Authentication for profile access on Android and iOS using NativePHP's bridge.
- [x] Implement Home Screen Widgets on Android (small and medium) and add iOS widget bridge + WidgetKit scaffold (`NativePHPWidgets`) with app-group storage sync.
- [x] Implement App Shortcuts / Quick Actions for Android and iOS on the app icon (iOS static quick actions now route to `/shortcut/clock-in` and `/shortcut/clock-out` through native deep-link handling).
- [x] Implement weekday local missing-entry reminders with timezone-aware scheduling and per-profile toggle control.
- [x] Implement iOS Critical Alerts for end-of-day under-hours reminders (added iOS native target scaffold, critical-alert entitlement, WKWebView bridge handler, and local under-hours scheduling path; production App Store rollout still depends on Apple critical-alert approval/codesigning on macOS).
- [x] Implement Native Share Sheet integration with profile-scoped CSV export and mobile share/download fallback.
- [x] Implement haptic feedback patterns for clock-in, clock-out, and destructive delete warning actions.
- [x] Implement Dynamic Type support with scalable rem/clamp typography and fluid layout behavior for larger text settings.
- [x] Implement Android back gesture handling so visible overlays/modals dismiss before app exit/navigation.
- [x] Implement custom iOS pull-to-refresh interaction on Dashboard with release threshold and visual refresh indicator.
- [x] Implement image optimization with responsive WebP assets and fallback rendering for logo branding.
- [x] Implement background refresh resilience for reminders on Android (boot/time-change restore), with iOS pending native scaffolding.

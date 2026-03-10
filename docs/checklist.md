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

## Phase 3: Frontend Views & Design System
- [x] Create `layouts/app.blade.php` structure emphasizing a mobile-only viewport.
- [x] Write a reset and customized stylesheet (`resources/css/app.css`):
    - Set dark/professional color variables (clean, no weird gradients, no emojis).
    - Enforce JetBrains Mono font.
    - Setup mobile-friendly layout and touch targets for buttons.
- [x] Apply CSS transitions and keyframe animations for interactions (buttons, modals, views).
- [x] Handle mobile safe-area insets (status bar, navigation bar) via NativePHP-injected `--inset-*` CSS custom properties to prevent system UI overlay.

## Phase 4: Application Features
- [x] **Dashboard (Home View):**
    - [x] Display Total Rendered Time & Total Rendered Days dynamically.
    - [x] Show action button (Time In / Time Out) based on current state.
    - [x] Display missing entries notification at the top of the screen if applicable.
- [x] **Calendar View:**
    - [x] Render a monthly grid layout using CSS grid.
    - [x] Highlight days uniquely if hours are rendered.
    - [x] Add touch interaction to reveal daily "Time In" and "Time Out" details.
- [x] **Settings:**
    - [x] Manual timezone selection (e.g. Asia/Manila for Philippines).
    - [x] Timezone-aware timestamps throughout the app (dashboard, calendar, time in/out).
    - [x] All times stored in UTC; converted to user timezone for display only.
    - [x] Settings page accessible from bottom navigation.
- [x] **Testing Transitions:** Ensure no glitchy or instant page loads; add subtle fading animations between Dashboard and Calendar.

## Phase 5: Build & Finalization
- [x] Ensure all assets are properly linked and optimized.
- [x] Simulate or test responsive dimensions.
- [x] Validate NativePHP integration (`php artisan native:serve` or `php artisan serve`).
- [x] Write feature tests for all core functionality (dashboard, time in/out, calendar, settings, timezone).

# HourLedger - Intern/OJT Tracker App

## Project Overview

HourLedger is a NativePHP mobile application designed specifically as an offline-first, local-only internship (OJT) tracker. It replaces manual timesheets by providing an intuitive tracking mechanism on mobile devices, helping interns manage their rendered hours and days conveniently.

### Key Goals
- **Simplicity:** Provide an easy-to-use interface to clock in and clock out daily.
- **Accuracy:** Track the aggregate hours and days rendered to ensure interns complete their OJT requirement.
- **Awareness:** Alert interns if they miss entries for previous days.
- **Shared Device Support:** Let multiple users share one device while keeping records completely isolated by profile.
- **Professional Aesthetics:** Deliver a pristine setup with support for both Dark and Light schemes using JetBrains Mono.

## System Architecture

The architecture represents a self-contained web app running inside a native mobile wrapper using NativePHP.

1. **Native Shell (NativePHP):** Serves the application to the user via an embedded server and webview, packaging the app for mobile operating systems (Android/iOS). Includes platform-specific optimizations (safe-area insets, theme-color, apple-mobile-web-app meta tags, touch-action, GPU-accelerated transforms).
2. **Backend Framework (Laravel PHP):**
   - Handles the business logic: calculating hours, storing in/out times, identifying missing entries.
   - Provides API endpoints or controller actions for the frontend.
3. **Database (SQLite):**
   - A singular local SQLite file residing in the app's local storage directory.
   - Synchronizes perfectly with NativePHP's embedded nature.
4. **Frontend (Blade + Vanilla CSS + JavaScript):**
   - Contains a mobile-only layout with rigid viewport bounds.
   - Powered by standard CSS for layout and animations, adhering to a strict professional palette.
   - Interactivity built strictly with smooth transitions for a dynamic mobile feel.
   - Includes an always-available profile switcher in the header for fast context switching.
5. **Font:** JetBrains Mono for system-wide typography.

## System Flow

1. **Launch:** The app initializes via NativePHP, pointing the webview to the entry route.
2. **Dashboard Review (Home):**
   - System checks SQLite database for entries tied to the currently active profile.
   - Calculates 'Total Rendered Time' and 'Total Rendered Days' and displays them.
   - Examines past dates dynamically to detect if any weekday is missing a time entry, injecting a native-feeling notification/alert banner at the top if true.
3. **Profile Switching:**
   - User switches profile from the header selector, or creates a new profile in the dedicated Profile panel.
   - Active profile is stored in session and all subsequent reads/writes use that profile.
4. **Profile Management:**
   - User can create, rename, archive, unarchive, and delete profiles from the Profile panel.
   - Archived profiles are excluded from the active switcher and cannot be selected.
   - Profile deletion is allowed only for non-default, non-active profiles; deleting a profile removes all records scoped to that profile.
   - Profile create/edit/archive/unarchive/delete actions use an in-app confirmation overlay to reduce accidental taps.
5. **Action (Time In/Out):**
   - Intern taps the central Call-To-Action (CTA).
   - The app records the current system timestamp in the database and computes the duration if clocking out.
6. **Calendar View Interaction:**
   - Intern navigates to the 'Calendar View'.
   - The app displays a monthly grid, highlighting days with logged hours.
   - Tapping a day fetches the specific "Time In" and "Time Out" data via a simple request or preloaded DOM.
7. **Navigation Persistence:**
   - Bottom navigation stays fixed and visible across views and scroll states.
   - Safe-area insets are applied so nav controls remain accessible on both Android and iOS.

## Data Model (Schema)

**`profiles` Table**
- `id` (Primary Key)
- `name` (String, Unique)
- `is_default` (Boolean)
- `is_archived` (Boolean)
- `created_at`, `updated_at` (Timestamps)

**`time_entries` Table**
- `id` (Primary Key)
- `profile_id` (Foreign-key-like reference to `profiles.id`)
- `date` (Date, Unique per profile)
- `time_in` (DateTime, Nullable)
- `time_out` (DateTime, Nullable)
- `total_minutes` (Integer, Nullable - computed after time_out)
- `created_at`, `updated_at` (Timestamps)

**`journals` Table**
- `id` (Primary Key)
- `profile_id` (Foreign-key-like reference to `profiles.id`)
- `date` (Date, Unique per profile)
- `content` (Text, Nullable)
- `created_at`, `updated_at` (Timestamps)

**`settings` Table**
- `id` (Primary Key)
- `profile_id` (Foreign-key-like reference to `profiles.id`)
- `key` (String, Unique per profile)
- `value` (String, Nullable)
- `created_at`, `updated_at` (Timestamps)

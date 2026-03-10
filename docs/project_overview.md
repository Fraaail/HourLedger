# HourLedger - Intern/OJT Tracker App

## Project Overview

HourLedger is a NativePHP mobile application designed specifically as an offline-first, local-only internship (OJT) tracker. It replaces manual timesheets by providing an intuitive tracking mechanism on mobile devices, helping interns manage their rendered hours and days conveniently.

### Key Goals
- **Simplicity:** Provide an easy-to-use interface to clock in and clock out daily.
- **Accuracy:** Track the aggregate hours and days rendered to ensure interns complete their OJT requirement.
- **Awareness:** Alert interns if they miss entries for previous days.
- **Professional Aesthetics:** Deliver a pristine setup using a dark/minimal scheme with JetBrains Mono, omitting emojis and inconsistent color schemes.

## System Architecture

The architecture represents a self-contained web app running inside a native mobile wrapper using NativePHP.

1. **Native Shell (NativePHP):** Serves the application to the user via an embedded server and webview, packaging the app for mobile operating systems (Android/iOS).
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
5. **Font:** JetBrains Mono for system-wide typography.

## System Flow

1. **Launch:** The app initializes via NativePHP, pointing the webview to the entry route.
2. **Dashboard Review (Home):**
   - System checks SQLite database for all entries.
   - Calculates 'Total Rendered Time' and 'Total Rendered Days' and displays them.
   - Examines past dates dynamically to detect if any weekday is missing a time entry, injecting a native-feeling notification/alert banner at the top if true.
3. **Action (Time In/Out):**
   - Intern taps the central Call-To-Action (CTA).
   - The app records the current system timestamp in the database and computes the duration if clocking out.
4. **Calendar View Interaction:**
   - Intern navigates to the 'Calendar View'.
   - The app displays a monthly grid, highlighting days with logged hours.
   - Tapping a day fetches the specific "Time In" and "Time Out" data via a simple request or preloaded DOM.

## Data Model (Schema)

**`time_entries` Table**
- `id` (Primary Key)
- `date` (Date, Unique)
- `time_in` (DateTime, Nullable)
- `time_out` (DateTime, Nullable)
- `total_minutes` (Integer, Nullable - computed after time_out)
- `created_at`, `updated_at` (Timestamps)

## Features to be Added in the Future

1. **Automatic Timezone based on Location:** The app automatically sets the correct timezone based on the user's location.
2. **Built-In Journal Tracker** Allows the user to create journals that is needed for OJT Reports.

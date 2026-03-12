# HourLedger

A NativePHP mobile application for tracking internship (OJT) hours. Offline-first, local-only time tracker built with Laravel, Blade, and SQLite.

## Features

- **Time In / Time Out** — One-tap clock in and clock out with automatic duration calculation.
- **Dashboard** — View total rendered hours and days at a glance.
- **Calendar View** — Monthly grid showing logged days with tap-to-reveal details.
- **Journal Entries** — Write daily journals for activities, integrated directly in the calendar view with missing entry indicators.
- **Missing Entry Alerts** — Notifications for weekdays without a completed time entry.
- **Timezone Selection** — Manually select your timezone (e.g. Asia/Manila) from settings. All times are stored in UTC and converted to your chosen timezone for display.
- **Theme Selector** — Choose between Dark, Light, and System themes. Includes instant preview and follows system preferences.
- **Mobile-Optimized** — JetBrains Mono typography, safe-area-aware layout optimized for both Android and iOS devices.

## Tech Stack

- **Framework:** Laravel 12 (PHP 8.4+)
- **Frontend:** Blade templates + vanilla CSS/JS
- **Database:** SQLite (local)
- **Mobile Shell:** NativePHP (Android / iOS)
- **Font:** JetBrains Mono
- **Testing:** Pest PHP

## Getting Started

```bash
# Install dependencies
npm install
composer install

# Run setup script (creates .env, generates key, creates DB, runs migrations)
php scripts/setup.php

# Start the development server
composer dev

# Or run as a native mobile app using Jump (Use --skip-build if there is one already)
composer require nativephp/mobile
php artisan native:jump
```

## Running Tests

```bash
php artisan test
```

## Mobile Layout

The app uses `viewport-fit=cover` to render edge-to-edge on mobile devices.

### Android
- Chrome address bar is themed via `theme-color` meta tag.
- NativePHP injects safe-area inset values as CSS custom properties (`--inset-top`, `--inset-right`, `--inset-bottom`, `--inset-left`) at runtime.

### iOS
- Full-screen web-app mode via `apple-mobile-web-app-capable` meta tags.
- Native `env(safe-area-inset-*)` CSS functions handle notch and home-indicator.
- Form inputs use `font-size: 16px` to prevent Safari auto-zoom.
- Momentum scrolling enabled via `-webkit-overflow-scrolling: touch`.
- All POST actions use JavaScript `fetch()` instead of native HTML forms to work around WKWebView silently dropping `HTTPBody` on form submissions.

### Both Platforms
- Dynamic viewport height (`100dvh`) avoids toolbar overlap.
- `overscroll-behavior` prevents rubber-band bouncing.
- `touch-action: manipulation` eliminates the 300ms tap delay.
- GPU-accelerated transforms on fixed elements for smooth 60fps scrolling.
- Minimum 48x48px touch targets per WCAG accessibility guidelines.

Default fallback values (`0px`) are declared in `:root` so the layout works correctly in a standard browser as well.

## Navigation

The bottom navigation provides access to three views:

- **Dashboard** — Main view with total rendered time/days and Time In/Out button.
- **Calendar** — Monthly grid with day-level time details on tap.
- **Settings** — Theme selection, timezone selection, and current time display.

## Project Documentation

- [Project Overview](docs/project_overview.md)
- [Implementation Checklist](docs/checklist.md)

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

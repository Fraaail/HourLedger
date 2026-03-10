# HourLedger

A NativePHP mobile application for tracking internship (OJT) hours. Offline-first, local-only time tracker built with Laravel, Blade, and SQLite.

## Features

- **Time In / Time Out** — One-tap clock in and clock out with automatic duration calculation.
- **Dashboard** — View total rendered hours and days at a glance.
- **Calendar View** — Monthly grid showing logged days with tap-to-reveal details.
- **Missing Entry Alerts** — Notifications for weekdays without a completed time entry.
- **Timezone Selection** — Manually select your timezone (e.g. Asia/Manila) from settings. All times are stored in UTC and converted to your chosen timezone for display.
- **Mobile-Optimized** — Dark-themed, JetBrains Mono typography, safe-area-aware layout that respects the device status bar and navigation bar.

## Tech Stack

- **Framework:** Laravel 12 (PHP)
- **Frontend:** Blade templates + vanilla CSS/JS
- **Database:** SQLite (local)
- **Mobile Shell:** NativePHP (Android)
- **Font:** JetBrains Mono
- **Testing:** Pest PHP

## Getting Started

```bash
# Install dependencies
composer install

# Run migrations
php artisan migrate

# Start the development server
php artisan serve

# Or run as a native mobile app
php artisan native:serve
```

## Running Tests

```bash
php artisan test
```

## Mobile Layout

The app uses `viewport-fit=cover` to render edge-to-edge on mobile devices. NativePHP injects safe-area inset values as CSS custom properties (`--inset-top`, `--inset-right`, `--inset-bottom`, `--inset-left`) at runtime, ensuring the app header and bottom navigation never overlap with the device’s status bar or navigation bar.

Default fallback values (`0px`) are declared in `:root` so the layout works correctly in a standard browser as well.

## Navigation

The bottom navigation provides access to three views:

- **Dashboard** — Main view with total rendered time/days and Time In/Out button.
- **Calendar** — Monthly grid with day-level time details on tap.
- **Settings** — Timezone selection and current time display.

## Project Documentation

- [Project Overview](docs/project_overview.md)
- [Implementation Checklist](docs/checklist.md)

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

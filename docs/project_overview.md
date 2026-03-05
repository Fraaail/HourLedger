# HourLedger - Project Overview

## About

**HourLedger** is a local-only NativePHP mobile application designed for interns and OJT trainees to track their daily rendered hours. The app provides a calendar-based view of time-in/time-out records, a dashboard with key metrics, and a notification system that alerts users about missing entries.

---

## Tech Stack

| Layer        | Technology                                                |
| ------------ | --------------------------------------------------------- |
| Backend      | Laravel 12, PHP 8.2+                                     |
| Frontend     | React 19, TypeScript, Tailwind CSS 4, shadcn/ui          |
| Bridge       | Inertia.js v2 (server-driven SPA)                        |
| Mobile       | NativePHP Mobile (Android packaging)                     |
| Native UI    | EDGE (NativePHP native component system)                 |
| Database     | SQLite (local, no remote server)                          |
| Routing      | Laravel Wayfinder (type-safe route generation)            |
| Font         | JetBrains Mono (monospace, clean, professional)           |
| Build Tool   | Vite 7                                                    |
| Linting      | ESLint 9, eslint-plugin-jsx-a11y, Prettier               |

---

## System Architecture

```
┌─────────────────────────────────────────────────────┐
│                   NativePHP Shell                    │
│                  (Android WebView)                   │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌───────────────────────────────────────────────┐  │
│  │           EDGE Native Components              │  │
│  │  (Rendered outside WebView by native layer)   │  │
│  │                                               │  │
│  │  ┌──────────┐          ┌──────────────────┐   │  │
│  │  │ TopBar   │          │   BottomNav      │   │  │
│  │  │ (native) │          │   (native)       │   │  │
│  │  └──────────┘          └──────────────────┘   │  │
│  └───────────────────────────────────────────────┘  │
│                                                     │
│  ┌───────────────────────────────────────────────┐  │
│  │              React 19 Frontend                │  │
│  │           (WebView content area)              │  │
│  │                                               │  │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────────┐  │  │
│  │  │Dashboard │ │ Calendar │ │Notifications │  │  │
│  │  │  Page    │ │   Page   │ │    Page      │  │  │
│  │  └──────────┘ └──────────┘ └──────────────┘  │  │
│  │                                               │  │
│  │  ┌──────────────────────────────────────────┐ │  │
│  │  │         Shared Components                │ │  │
│  │  │  MetricCard, CalendarGrid, DayCell,      │ │  │
│  │  │  TimeEntryModal, NotificationCard,       │ │  │
│  │  │  ClockButton, StatusBadge                │ │  │
│  │  └──────────────────────────────────────────┘ │  │
│  │                                               │  │
│  │  ┌──────────────────────────────────────────┐ │  │
│  │  │      Inertia.js Bridge Layer             │ │  │
│  │  └──────────────────────────────────────────┘ │  │
│  └───────────────────────────────────────────────┘  │
│                                                     │
│  ┌───────────────────────────────────────────────┐  │
│  │            Laravel 12 Backend                 │  │
│  │                                               │  │
│  │  ┌──────────────────────────────────────────┐ │  │
│  │  │           Middleware                     │ │  │
│  │  │  SetEdgeComponents (adds native UI)      │ │  │
│  │  │  RenderEdgeComponents (sends to native)  │ │  │
│  │  └──────────────────────────────────────────┘ │  │
│  │                                               │  │
│  │  ┌──────────────────────────────────────────┐ │  │
│  │  │           Controllers                    │ │  │
│  │  │  DashboardController                     │ │  │
│  │  │  TimeEntryController                     │ │  │
│  │  │  NotificationController                  │ │  │
│  │  └──────────────────────────────────────────┘ │  │
│  │                                               │  │
│  │  ┌──────────────────────────────────────────┐ │  │
│  │  │            Models                        │ │  │
│  │  │  TimeEntry                               │ │  │
│  │  │  MissingEntryNotification                │ │  │
│  │  └──────────────────────────────────────────┘ │  │
│  │                                               │  │
│  │  ┌──────────────────────────────────────────┐ │  │
│  │  │     SQLite Database (Local)              │ │  │
│  │  │  time_entries, notifications             │ │  │
│  │  └──────────────────────────────────────────┘ │  │
│  └───────────────────────────────────────────────┘  │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## EDGE Component System

EDGE is NativePHP for Mobile's native component system. It renders UI elements
**outside** the WebView as actual native Android/iOS components, providing
better performance and a truly native look.

### How It Works

1. The `SetEdgeComponents` middleware programmatically registers native UI
   components (BottomNav, TopBar) on every HTTP request using `Edge::add()`.
2. NativePHP's built-in `RenderEdgeComponents` middleware (auto-registered by
   `NativeServiceProvider`) calls `Edge::set()` after the response, which sends
   the component tree to the native layer via `nativephp_call('Edge.Set', ...)`.
3. The native shell renders these components outside the WebView, overlaying or
   adjacent to the web content area.

### Available EDGE Components

| Component          | Blade Tag                    | Description                      |
| ------------------ | ---------------------------- | -------------------------------- |
| BottomNav          | `<native:bottom-nav>`        | Bottom tab bar (container)       |
| BottomNavItem      | `<native:bottom-nav-item>`   | Individual tab in bottom nav     |
| TopBar             | `<native:top-bar>`           | Top app bar with title           |
| TopBarAction       | `<native:top-bar-action>`    | Action button in top bar         |
| Fab                | `<native:fab>`               | Floating action button           |
| SideNav            | `<native:side-nav>`          | Side navigation drawer           |
| SideNavHeader      | `<native:side-nav-header>`   | Header in side nav               |
| SideNavGroup       | `<native:side-nav-group>`    | Grouped items in side nav        |
| SideNavItem        | `<native:side-nav-item>`     | Individual item in side nav      |
| HorizontalDivider  | `<native:horizontal-divider>`| Divider line                     |

### Usage in HourLedger

HourLedger uses **programmatic EDGE registration** (not Blade tags) because the
app uses Inertia.js with React. Blade views are only rendered on the initial page
load; subsequent navigations are XHR-based. The middleware approach ensures native
components are updated on every request.

```php
// app/Http/Middleware/SetEdgeComponents.php
$contextIndex = Edge::startContext();
Edge::add('bottom_nav_item', ['id' => 'dashboard', 'icon' => 'dashboard', ...]);
Edge::add('bottom_nav_item', ['id' => 'calendar', 'icon' => 'calendar_month', ...]);
Edge::add('bottom_nav_item', ['id' => 'notifications', 'icon' => 'notifications', ...]);
Edge::endContext($contextIndex, 'bottom_nav', ['label_visibility' => 'labeled', ...]);
```

---

## Database Schema

### `time_entries` table

| Column       | Type      | Description                                |
| ------------ | --------- | ------------------------------------------ |
| id           | INTEGER   | Primary key, auto-increment                |
| date         | DATE      | The date of the entry (unique)             |
| time_in      | DATETIME  | Clock-in timestamp                         |
| time_out     | DATETIME  | Clock-out timestamp (nullable)             |
| notes        | TEXT      | Optional notes for the day (nullable)      |
| created_at   | TIMESTAMP | Laravel timestamp                          |
| updated_at   | TIMESTAMP | Laravel timestamp                          |

### `notifications` table

| Column       | Type      | Description                                |
| ------------ | --------- | ------------------------------------------ |
| id           | INTEGER   | Primary key, auto-increment                |
| type         | VARCHAR   | Notification type (e.g., `missing_entry`)  |
| title        | VARCHAR   | Notification title                         |
| message      | TEXT      | Notification body                          |
| date         | DATE      | The date the notification references       |
| is_read      | BOOLEAN   | Read status, default false                 |
| created_at   | TIMESTAMP | Laravel timestamp                          |
| updated_at   | TIMESTAMP | Laravel timestamp                          |

---

## System Flow

### 1. App Launch Flow

```
App Opens
  → NativePHP loads Laravel server
  → Laravel serves "/" route
  → Inertia renders Dashboard page
  → Backend checks for missing entries since last visit
  → Missing entry notifications are generated
  → Dashboard displays metrics + unread notification count
```

### 2. Clock-In Flow

```
User taps "Clock In" on Dashboard
  → POST /time-entries/clock-in
  → Backend creates TimeEntry with current timestamp as time_in
  → Backend returns updated dashboard data
  → UI animates state change (button transitions to "Clock Out")
  → Dashboard metrics update in real-time
```

### 3. Clock-Out Flow

```
User taps "Clock Out" on Dashboard
  → POST /time-entries/clock-out
  → Backend updates today's TimeEntry with current timestamp as time_out
  → Backend calculates rendered hours for the day
  → UI animates state change (button transitions to "Clocked Out")
  → Dashboard metrics update
```

### 4. Calendar View Flow

```
User navigates to Calendar tab
  → GET /calendar?month=YYYY-MM
  → Backend fetches all TimeEntry records for the month
  → Frontend renders calendar grid with color-coded days:
     - Green: Complete entry (time_in + time_out)
     - Yellow: Incomplete entry (time_in only)
     - Red border: Missing entry (weekday with no record)
     - Gray: Weekend / future date
  → User taps a day to view/edit entry details
  → Modal slides up with time-in, time-out, notes
```

### 5. Notification Flow

```
On each app launch / dashboard visit:
  → Backend scans weekdays from first entry to today
  → Identifies days with no TimeEntry record
  → Creates MissingEntryNotification for each missing day
  → Notification bell shows unread count badge
  → User taps notification to navigate to that day's entry form
  → Marking notification as read dims it in the list
```

### 6. Manual Entry / Edit Flow

```
User taps a date on Calendar (or notification link)
  → Modal opens with time-in/time-out fields
  → User enters/edits times manually
  → POST /time-entries (create) or PUT /time-entries/{id} (update)
  → Backend validates and saves
  → Calendar view updates
  → Related notification marked as resolved
```

---

## Page Structure

### 1. Dashboard (`/`)

The main landing page. Displays:

- **Greeting header** with current date
- **Clock-In / Clock-Out button** (large, prominent, context-aware)
- **Today's status card** showing time-in, time-out, hours rendered today
- **Metric cards**:
  - Total Rendered Hours (all-time)
  - Total Rendered Days (count of complete entries)
  - Average Hours Per Day
  - Current Week Hours
- **Unread notification count** in the bottom nav badge

### 2. Calendar (`/calendar`)

Monthly calendar view:

- **Month/year header** with previous/next navigation arrows
- **Day-of-week labels** (Mon-Sun)
- **Day cells** with color-coded status indicators
- **Tap interaction** opens a slide-up modal for that day's entry
- **Entry modal** contains:
  - Date display
  - Time-in picker
  - Time-out picker
  - Notes textarea
  - Save / Delete buttons

### 3. Notifications (`/notifications`)

Scrollable list of notifications:

- **Header** with notification count
- **Mark all as read** action button
- **Notification cards** showing:
  - Type icon (warning for missing entry)
  - Title and message
  - Referenced date
  - Read/unread visual state
  - Tap to navigate to entry form for that date

---

## Navigation

Navigation is handled by **native EDGE components** rendered outside the WebView
for a truly native look and feel. The `SetEdgeComponents` middleware
programmatically registers these on every request.

### Native BottomNav (EDGE)

| Tab           | Icon (Material) | Route           | Badge            |
| ------------- | ---------------- | --------------- | ---------------- |
| Dashboard     | `dashboard`      | `/`             | —                |
| Calendar      | `calendar_month` | `/calendar`     | —                |
| Alerts        | `notifications`  | `/notifications`| Unread count     |

### Native TopBar (EDGE)

Contextual title bar rendered natively. Title resolves based on current route:
- `/` → "HourLedger"
- `/calendar` → "Calendar"
- `/notifications` → "Notifications"

### EDGE Middleware Flow

```
Request → SetEdgeComponents middleware
  ├─ Edge::startContext() → bottom_nav context
  ├─ Edge::add(bottom_nav_item, {...}) × 3 tabs
  ├─ Edge::endContext() → closes bottom_nav
  ├─ Edge::add(top_bar, {...})
  └─ passes to next middleware
        ↓
  Controller handles request
        ↓
  RenderEdgeComponents middleware (auto-registered by NativePHP)
  └─ Edge::set() → sends component tree to native layer via nativephp_call()
```

---

## Design System

### Principles

- **Clean and professional** appearance
- **No gradients**, no rainbow colors, no emojis
- **Monochrome with accent** color scheme (neutral grays + single accent)
- **Mobile-first** - every element sized for touch targets (min 44px)
- **Consistent spacing** using Tailwind's spacing scale

### Typography

- **Font family**: JetBrains Mono (all text)
- **Weights**: 400 (regular), 500 (medium), 600 (semibold), 700 (bold)
- **Scale**: Text sizes from `text-xs` (12px) to `text-2xl` (24px)

### Color Palette

Uses the existing shadcn/ui CSS variable system:

| Purpose         | Light Mode          | Dark Mode           |
| --------------- | ------------------- | ------------------- |
| Background      | White               | Near-black          |
| Foreground      | Near-black          | Near-white          |
| Primary         | Dark gray           | Light gray          |
| Secondary       | Light gray          | Dark gray           |
| Muted           | Soft gray           | Medium gray         |
| Accent (green)  | `oklch(0.65 0.15 145)` | `oklch(0.55 0.15 145)` |
| Warning (amber) | `oklch(0.75 0.15 85)` | `oklch(0.65 0.15 85)` |
| Destructive     | Existing red        | Existing red        |

### Animations & Transitions

Every interactive element includes motion feedback:

| Interaction          | Animation                                      |
| -------------------- | ---------------------------------------------- |
| Page navigation      | Slide transition (left/right based on tab)     |
| Button press         | Scale down on press (0.97), spring back         |
| Card appear          | Fade-in + slide-up on mount                    |
| Modal open           | Slide up from bottom + backdrop fade            |
| Modal close          | Slide down + backdrop fade out                  |
| Clock-in/out toggle  | Morph transition between states                 |
| Calendar day tap     | Ripple effect + slight scale                    |
| Notification read    | Fade opacity change                             |
| Metric card load     | Staggered fade-in (100ms delay between cards)   |
| Pull to refresh      | Smooth scroll indicator                        |
| Tab switch           | Bottom nav icon scale + label slide             |

### Touch Optimization

- All tap targets minimum 44x44px
- Bottom navigation bar height: 64px with safe area padding
- Cards have generous padding (16-20px)
- Form inputs are 48px tall
- Swipe gestures supported where appropriate

---

## API Endpoints

### Time Entries

| Method | Endpoint                    | Description                    |
| ------ | --------------------------- | ------------------------------ |
| GET    | `/`                         | Dashboard with metrics         |
| POST   | `/time-entries/clock-in`    | Record clock-in for today      |
| POST   | `/time-entries/clock-out`   | Record clock-out for today     |
| GET    | `/calendar`                 | Calendar view for a month      |
| POST   | `/time-entries`             | Create/update a manual entry   |
| PUT    | `/time-entries/{id}`        | Update an existing entry       |
| DELETE | `/time-entries/{id}`        | Delete an entry                |

### Notifications

| Method | Endpoint                              | Description              |
| ------ | ------------------------------------- | ------------------------ |
| GET    | `/notifications`                      | List all notifications   |
| POST   | `/notifications/{id}/mark-read`       | Mark single as read      |
| POST   | `/notifications/mark-all-read`        | Mark all as read         |

---

## File Structure (New Files)

```
app/
  Http/
    Middleware/
      SetEdgeComponents.php       <-- EDGE native UI middleware
  Models/
    TimeEntry.php
    EntryNotification.php
  Http/
    Controllers/
      DashboardController.php
      TimeEntryController.php
      NotificationController.php

bootstrap/
  app.php                         <-- registers SetEdgeComponents middleware

database/
  migrations/
    xxxx_xx_xx_create_time_entries_table.php
    xxxx_xx_xx_create_notifications_table.php

resources/
  css/
    app.css                       <-- native-content-area, mobile utilities
  js/
    components/
      hourledger/
        metric-card.tsx
        calendar-grid.tsx
        day-cell.tsx
        time-entry-modal.tsx
        notification-card.tsx
        clock-button.tsx
        status-badge.tsx
    layouts/
      mobile-layout.tsx           <-- EDGE-aware (no web bottom nav)
    pages/
      dashboard.tsx
      calendar.tsx
      notifications.tsx
    hooks/
      use-time-format.ts
    types/
      hourledger.ts
  views/
    app.blade.php                 <-- mobile-optimized viewport

routes/
  web.php

docs/
  project_overview.md
  checklist.md
```

---

## Security & Privacy

- **All data stays local** on the device in SQLite
- No network requests for tracking data
- No cloud sync, no telemetry
- Auth is handled locally through Laravel Fortify (existing)
- Single-user mode (the device owner is the sole user)

---

## Performance Considerations

- **EDGE native nav** eliminates WebView rendering for BottomNav and TopBar
- SQLite queries are fast for single-user local data
- Inertia.js partial reloads minimize data transfer
- React 19 compiler optimizes re-renders automatically
- Tailwind CSS purges unused styles in production
- NativePHP bundles everything into the APK
- Calendar renders only visible month (lazy loading for months)
- Vite chunk splitting separates vendor/framework code for faster initial loads
- Mobile viewport locked (`user-scalable=no`, `viewport-fit=cover`)
- CSS utility classes for native-like scroll behavior (`overscroll-behavior-y: contain`)
- JSX accessibility linting via `eslint-plugin-jsx-a11y`

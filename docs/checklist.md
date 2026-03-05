# HourLedger - Implementation Checklist

## Phase 0: EDGE Native UI Setup

### EDGE Middleware

- [x] Create `SetEdgeComponents` middleware with native BottomNav and TopBar
- [x] Register `SetEdgeComponents` in `bootstrap/app.php` web middleware
- [x] Verify `RenderEdgeComponents` is auto-registered by NativePHP
- [x] Configure BottomNav with 3 tabs: Dashboard, Calendar, Alerts
- [x] Configure TopBar with contextual page titles
- [x] Add notification badge count on Alerts tab
- [x] Set active state on BottomNav based on current URL

### Mobile Viewport & Layout

- [x] Update `app.blade.php` viewport meta: `viewport-fit=cover`, `user-scalable=no`
- [x] Add `native-content-area` CSS class for EDGE-aware padding
- [x] Add `mobile-no-select` and `native-scroll` utility classes
- [x] Update `mobile-layout.tsx` to remove web-based bottom nav (EDGE handles it)

### Tooling

- [x] Install and configure `eslint-plugin-jsx-a11y` for mobile accessibility
- [x] Add mobile-relevant a11y rules to ESLint config
- [x] Configure Vite build for mobile (chunk splitting, ES2020 target)

---

## Phase 1: Foundation

### Database

- [ ] Create migration: `time_entries` table (id, date, time_in, time_out, notes, timestamps)
- [ ] Create migration: `notifications` table (id, type, title, message, date, is_read, timestamps)
- [ ] Run migrations to verify schema

### Models

- [ ] Create `TimeEntry` model with fillable fields, casts, and date accessors
- [ ] Create `EntryNotification` model with fillable fields and casts
- [ ] Add helper methods to `TimeEntry` (rendered hours calculation, status check)

---

## Phase 2: Backend Logic

### Controllers

- [ ] Create `DashboardController` - index method with metrics aggregation
- [ ] Create `TimeEntryController` - clock-in, clock-out, store, update, destroy methods
- [ ] Create `NotificationController` - index, mark-read, mark-all-read methods

### Routes

- [ ] Update `routes/web.php` with new HourLedger routes
- [ ] Register dashboard route `/` to `DashboardController`
- [ ] Register time entry routes (`/time-entries/*`)
- [ ] Register calendar route (`/calendar`)
- [ ] Register notification routes (`/notifications/*`)
- [ ] Run `php artisan wayfinder:generate` for type-safe route generation

### Notification Logic

- [ ] Implement missing entry detection (scan weekdays from first entry to today)
- [ ] Auto-generate `missing_entry` notifications on dashboard load
- [ ] Avoid duplicate notifications for already-flagged dates
- [ ] Mark related notification as resolved when entry is created

---

## Phase 3: Frontend Foundation

### Design System Setup

- [ ] Add JetBrains Mono font import to `app.blade.php`
- [ ] Update `app.css` to use JetBrains Mono as primary font
- [ ] Add custom CSS variables for accent colors (green, amber)
- [ ] Define animation keyframes and transition classes

### Mobile Layout

- [x] Create `mobile-layout.tsx` - EDGE-aware layout shell (no web bottom nav)
- [ ] Add safe area padding for native EDGE components
- [ ] Ensure all touch targets are minimum 44x44px

### Shared Components

- [ ] Create `metric-card.tsx` - animated stat card with label and value
- [ ] Create `status-badge.tsx` - color-coded status indicator
- [ ] Create `clock-button.tsx` - large clock-in/out toggle button with animation

---

## Phase 4: Dashboard Page

- [ ] Replace existing `dashboard.tsx` with HourLedger dashboard
- [ ] Add greeting header with current date display
- [ ] Implement clock-in/clock-out button with state management
- [ ] Display today's status card (time-in, time-out, hours today)
- [ ] Add metric cards row:
  - [ ] Total Rendered Hours
  - [ ] Total Rendered Days
  - [ ] Average Hours Per Day
  - [ ] Current Week Hours
- [ ] Add staggered fade-in animation for metric cards
- [ ] Wire up Inertia form submissions for clock-in/clock-out
- [ ] Show notification badge count in bottom nav

---

## Phase 5: Calendar Page

- [ ] Create `calendar.tsx` page
- [ ] Create `calendar-grid.tsx` - monthly grid component
- [ ] Create `day-cell.tsx` - individual day cell with status colors
  - [ ] Green: Complete entry (time_in + time_out)
  - [ ] Yellow/amber: Incomplete entry (time_in only)
  - [ ] Red border: Missing entry (weekday, no record)
  - [ ] Gray: Weekend or future date
- [ ] Add month navigation (previous/next arrows)
- [ ] Create `time-entry-modal.tsx` - slide-up modal for entry details
  - [ ] Date display
  - [ ] Time-in input field
  - [ ] Time-out input field
  - [ ] Notes textarea
  - [ ] Save button
  - [ ] Delete button (for existing entries)
- [ ] Wire up modal form submissions via Inertia
- [ ] Add calendar day tap animation (scale + ripple)
- [ ] Add modal slide-up/down transition

---

## Phase 6: Notifications Page

- [ ] Create `notifications.tsx` page
- [ ] Create `notification-card.tsx` - individual notification display
- [ ] Add "Mark all as read" button in header
- [ ] Show notification type icon (warning for missing entries)
- [ ] Display notification title, message, and referenced date
- [ ] Visual distinction between read/unread (opacity change)
- [ ] Tap notification to navigate to calendar entry for that date
- [ ] Wire up mark-read and mark-all-read actions
- [ ] Add fade transition for read state change
- [ ] Show empty state when no notifications exist

---

## Phase 7: Animations & Polish

### Page Transitions

- [ ] Implement slide-left/slide-right transition between tabs
- [ ] Add fade-in for initial page load

### Micro-interactions

- [ ] Button press scale effect (0.97 scale on press, spring back)
- [ ] Card mount animation (fade-in + slide-up)
- [ ] Modal backdrop fade-in/out
- [ ] Clock button morph between states
- [ ] Tab switch icon animation
- [ ] Notification card read state fade

### Performance

- [ ] Verify React 19 compiler optimization is active
- [ ] Ensure smooth 60fps animations on mobile
- [ ] Lazy load calendar data by month
- [ ] Minimize bundle size for mobile
- [ ] Verify EDGE native nav renders correctly on device
- [ ] Confirm Vite chunk splitting works in production build

---

## Phase 8: Integration & Testing

- [ ] Test full clock-in / clock-out flow
- [ ] Test manual entry creation via calendar
- [ ] Test manual entry editing
- [ ] Test manual entry deletion
- [ ] Test notification generation for missing days
- [ ] Test notification mark-as-read
- [ ] Test calendar month navigation
- [ ] Test dark mode consistency
- [ ] Test on mobile viewport (360px - 428px width)
- [ ] Test EDGE BottomNav tab switching on device
- [ ] Test EDGE TopBar title changes across routes
- [ ] Test EDGE notification badge updates after mark-read
- [ ] Run `npm run lint` to verify no accessibility violations
- [ ] Run `npm run build` to verify production build
- [ ] Run `php artisan native:run android` to verify NativePHP packaging

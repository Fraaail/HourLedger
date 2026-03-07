# HourLedger

HourLedger is a local, lightweight time-tracking and calendaring application. Built to feel seamless and responsive, it helps you clock in, clock out, review your timeline in a calendar view, and manage past time entries with ease.

---

## 🚀 Initial Setup

Follow these steps to get your HourLedger environment up and running smoothly.

### Prerequisites

Ensure you have the following installed on your machine:
- [PHP](https://www.php.net/downloads) (≥ 8.2)
- [Composer](https://getcomposer.org/)
- [Node.js & npm](https://nodejs.org/en)

### Quick Setup

We've provided a simple command to handle environment generation, dependency installation, and database migration in one go.

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Fraaail/HourLedger.git
   cd HourLedger
   ```

2. **Run the Setup Script:**
   ```bash
   composer run setup
   ```
   *This command will install PHP dependencies, create your `.env` file, generate an app key, prepare your SQLite database, install npm packages, and build the frontend assets.*

3. **Start the Development Server:**
   ```bash
   composer run dev
   ```
   *This concurrently starts the Laravel server, a queue worker, and the Vite development server. You can now access your app at `http://localhost:8000`.*

---

## 🧩 System Components

The architecture relies on a modern, monolithic ecosystem bridging robust server-side logic and highly interactive client-side interfaces.

- **[Laravel 12.0](https://laravel.com/)**: Serves as the robust backend framework, managing SQLite database interactions, business logic, endpoints, and background jobs.
- **[React 19](https://react.dev/)**: Drives the user interface, offering real-time component updates and a smooth, reactive frontend experience.
- **[Inertia.js](https://inertiajs.com/)**: The glue between Laravel and React. It takes the server-side routing and passes data directly to React components without needing an intermediate REST or GraphQL API, achieving a Single-Page Application (SPA) feel.
- **[Tailwind CSS v4](https://tailwindcss.com/) & [Radix UI](https://www.radix-ui.com/)**: Used for rapid, utility-first styling combined with unstyled, completely accessible UI primitives for dropdowns, dialogues, and tooltips.
- **[Vite](https://vitejs.dev/)**: Bundles and serves the frontend assets (JavaScript and CSS) with lightning-fast Hot Module Replacement (HMR) during development.
- **SQLite**: A lightweight, file-based database configured right out of the box to store time entries and configurations locally.
- **NativePHP** *(Optional/Target)*: Provides the wrapper and capabilities to package this web application into a fully-fledged desktop or mobile application later.

---

## ⚙️ How the System Works

HourLedger bypasses the traditional decoupled API architecture in favor of an integrated full-stack monolith pattern. Here is how the application typically processes a user's request:

1. **Routing and Controllers (Backend):**
   When you navigate to a page (e.g., the local Dashboard or Calendar), the HTTP requested is routed through Laravel's `routes/web.php` to a specific Controller (like `TimeEntryController` or `DashboardController`).

2. **Data Fetching:**
   The controller interacts with Eloquent ORM (Laravel's database layer) to securely interact with the local SQLite database. It calculates active time entries, builds a timeline, and retrieves related notifications.

3. **Inertia Response (The Bridge):**
   Instead of returning a standard HTML view or a raw JSON payload, the controller returns an Inertia response (`Inertia::render`). This packages the queried data as **props** and specifies which React Component should be rendered on the frontend.

4. **React Rendering (Frontend):**
   Vite captures the response and seamlessly mounts the React components. Because Inertia tracks the routing state, subsequent clicks (like clocking in or marking a notification read) use XHR requests to fetch new Inertia payloads. The page never fully reloads, rendering smoothly like a modern SPA while still utilizing server-side routing.

5. **Local First:**
   Auth flows are mostly sidestepped natively since HourLedger runs locally. Native PHP (if bundled) manages spinning up the underlying PHP server in the background, interacting natively with your OS.

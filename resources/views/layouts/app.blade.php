<!DOCTYPE html>
<html lang="en" class="theme-{{ \App\Models\Setting::get('theme', 'dark') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>HourLedger</title>
    <!-- iOS: full-screen web app mode -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="HourLedger">
    <!-- Android: Chrome address bar theming -->
    <meta name="theme-color" content="#0d1117" id="theme-meta">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <!-- JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
    <div id="app" class="fade-in">
        <header class="app-header">
            <h1>HourLedger</h1>
        </header>

        <main class="app-main">
            @yield('content')
        </main>

        <nav class="bottom-nav">
            <a href="{{ route('dashboard', [], false) }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <div class="nav-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="3" y1="9" x2="21" y2="9"></line>
                        <line x1="9" y1="21" x2="9" y2="9"></line>
                    </svg>
                </div>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('calendar', [], false) }}" class="nav-item {{ request()->routeIs('calendar') ? 'active' : '' }}">
                <div class="nav-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <span>Calendar</span>
            </a>
            <a href="{{ route('settings', [], false) }}" class="nav-item {{ request()->routeIs('settings') ? 'active' : '' }}">
                <div class="nav-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                </div>
                <span>Settings</span>
            </a>
        </nav>
    </div>
    <script>
        (function() {
            const theme = "{{ \App\Models\Setting::get('theme', 'dark') }}";
            const html = document.documentElement;
            const meta = document.getElementById('theme-meta');

            function updateTheme(val) {
                html.classList.remove('theme-dark', 'theme-light', 'theme-system');
                html.classList.add('theme-' + val);

                if (meta) {
                    if (val === 'light' || (val === 'system' && window.matchMedia('(prefers-color-scheme: light)').matches)) {
                        meta.setAttribute('content', '#f6f8fa');
                    } else {
                        meta.setAttribute('content', '#0d1117');
                    }
                }
            }

            updateTheme(theme);

            if (theme === 'system') {
                window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', e => {
                    updateTheme('system');
                });
            }

            window.addEventListener('theme-changed', (e) => {
                updateTheme(e.detail.theme);
            });
        })();

        // Warm route responses to reduce perceived delay on mobile tab switches.
        (function prefetchBottomNav() {
            const links = document.querySelectorAll('.bottom-nav .nav-item');
            links.forEach((link) => {
                link.addEventListener('touchstart', () => {
                    const href = link.getAttribute('href');
                    if (!href) {
                        return;
                    }

                    fetch(href, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).catch(() => {
                        // Ignore prefetch failures; regular navigation still works.
                    });
                }, { once: true, passive: true });
            });
        })();
    </script>
</body>
</html>

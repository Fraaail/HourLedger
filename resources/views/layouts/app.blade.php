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
    <!-- JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
    <div id="app" class="fade-in">
        <header class="app-header">
            <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <img src="{{ asset('logo.svg') }}" alt="HourLedger Logo" style="width: 28px; height: 28px;" />
                <h1 style="margin: 0;">HourLedger</h1>
            </div>
            @if(isset($profiles, $activeProfile))
                <div style="display: flex; justify-content: center; margin-top: 0.5rem;">
                    <button type="button" class="profile-header-btn" onclick="openProfileSwitcher()">
                        <div class="profile-header-avatar">
                            {{ strtoupper(substr($activeProfile->name, 0, 1)) }}
                        </div>
                        <span class="profile-header-name">{{ $activeProfile->name }}</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.6">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                </div>
            @endif
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
            <a href="{{ route('profiles.index', [], false) }}" class="nav-item {{ request()->routeIs('profiles.*') ? 'active' : '' }}">
                <div class="nav-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <span>Profile</span>
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

        @if(isset($profiles, $activeProfile))
        <div class="modal-overlay profile-switcher-sheet" id="profileSwitcherModal" onclick="if(event.target===this) closeProfileSwitcher()">
            <div class="modal-content">
                <div class="sheet-handle"></div>
                <h3 class="modal-title" style="text-align: left; margin-bottom: 0.25rem;">Switch Profile</h3>
                <p class="modal-body" style="text-align: left; margin-bottom: 1.5rem;">Select an active profile.</p>
                <div class="profile-list-container">
                    @foreach($profiles as $profile)
                        <button type="button" class="profile-list-btn {{ $activeProfile->id === $profile->id ? 'active' : '' }}" onclick="switchActiveProfile('{{ $profile->id }}')">
                            <div class="profile-list-avatar">
                                {{ strtoupper(substr($profile->name, 0, 1)) }}
                            </div>
                            <span class="profile-list-name">{{ $profile->name }}</span>
                            @if($activeProfile->id === $profile->id)
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
                <button type="button" class="btn-modal cancel" style="margin-top: 1rem; width: 100%;" onclick="closeProfileSwitcher()">Cancel</button>
            </div>
        </div>
        @endif
    </div>
    <script>
        (function() {
            const theme = "{{ \App\Models\Setting::get('theme', 'dark') }}";
            const html = document.documentElement;
            const meta = document.getElementById('theme-meta');
            const hapticPatterns = {
                clock_in_success: [20, 30, 20],
                clock_out_completion: [40, 45, 40],
                deletion_warning: [80, 45, 80],
            };

            window.triggerHapticFeedback = function(type) {
                if (typeof navigator === 'undefined' || typeof navigator.vibrate !== 'function') {
                    return false;
                }

                const pattern = hapticPatterns[type];

                if (!pattern) {
                    return false;
                }

                return navigator.vibrate(pattern);
            };

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

            window.openProfileSwitcher = function() {
                document.getElementById('profileSwitcherModal').classList.add('visible');
            };

            window.closeProfileSwitcher = function() {
                document.getElementById('profileSwitcherModal').classList.remove('visible');
            };

            window.switchActiveProfile = function(profileId) {
                fetch('{{ route('profiles.switch', [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: '_token={{ csrf_token() }}&profile_id=' + encodeURIComponent(profileId)
                }).then(async function(response) {
                    const data = await response.json();
                    if (data.requires_biometrics) {
                        // The controller triggered the prompt. Now we wait for the native event.
                        const handler = function(e) {
                            if (e.detail.id === data.biometric_id) {
                                window.removeEventListener('nativephp:Native\\Mobile\\Events\\Biometric\\Completed', handler);
                                if (e.detail.success) {
                                    // Successfully authenticated, retry the switch
                                    switchActiveProfile(profileId);
                                } else {
                                    closeProfileSwitcher();
                                }
                            }
                        };
                        window.addEventListener('nativephp:Native\\Mobile\\Events\\Biometric\\Completed', handler);
                        return;
                    }
                    window.location.reload();
                }).catch(function() {
                    window.location.reload();
                });
            };
        })();
    </script>
</body>
</html>

@extends('layouts.app')

@section('content')

@if(count($missingEntries) > 0)
<div class="notification">
    <div class="notification-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        Missing Entries
    </div>
    <ul style="margin-top: 4px; padding-left: 1.5rem;">
        @foreach(array_slice($missingEntries, -3) as $missing)
            <li>{{ \Carbon\Carbon::parse($missing)->format('M d, Y') }}</li>
        @endforeach
        @if(count($missingEntries) > 3)
            <li>and {{ count($missingEntries) - 3 }} more...</li>
        @endif
    </ul>
</div>
@endif

<div id="pullToRefreshIndicator" class="pull-refresh-indicator" aria-live="polite" aria-hidden="true">
    <span class="pull-refresh-spinner" aria-hidden="true"></span>
    <span id="pullToRefreshText">Pull to refresh</span>
</div>

<div class="metric-cards">
    <div class="metric-card">
        <h3>Total Rendered</h3>
        <div class="value">{{ number_format($totalMinutes / 60, 1) }}<span style="font-size: 0.9rem; color: var(--text-secondary)">h</span></div>
    </div>
    <div class="metric-card">
        <h3>Rendered Days</h3>
        <div class="value">{{ $totalDays }}</div>
    </div>
</div>

<div class="action-area">
    @if(!$entryToday || !$entryToday->time_in)
        <button type="button" class="btn-time in" id="clockBtn" onclick="submitClock('{{ route('time.in', [], false) }}')">
            TIME IN
            <span class="helper">{{ now()->timezone($tz)->format('h:i A') }}</span>
        </button>
    @elseif($entryToday && !$entryToday->time_out)
        <button type="button" class="btn-time out" id="clockBtn" onclick="submitClock('{{ route('time.out', [], false) }}')">
            TIME OUT
            <span class="helper">In at {{ $entryToday->time_in->timezone($tz)->format('h:i A') }}</span>
        </button>
    @else
        <button type="button" class="btn-time" disabled>
            COMPLETED
            <span class="helper">{{ number_format($entryToday->total_minutes / 60, 1) }}h today</span>
        </button>
    @endif
</div>

<div class="modal-overlay" id="confirmModal" data-back-close-handler="closeModal">
    <div class="modal-content">
        <div class="modal-title" id="modalTitle">Confirm Action</div>
        <div class="modal-body" id="modalBody">Are you sure you want to proceed?</div>
        <div class="modal-actions">
            <button type="button" class="btn-modal confirm" id="modalConfirmBtn">CONFIRM</button>
            <button type="button" class="btn-modal cancel" onclick="closeModal()">CANCEL</button>
        </div>
    </div>
</div>

<script>
let currentUrl = '';
let currentHapticType = null;
const appMain = document.querySelector('.app-main');
const pullIndicator = document.getElementById('pullToRefreshIndicator');
const pullText = document.getElementById('pullToRefreshText');

const pullToRefresh = {
    enabled: false,
    active: false,
    armed: false,
    startY: 0,
    distance: 0,
    refreshing: false,
};

const PULL_THRESHOLD = 82;
const PULL_MAX = 130;

const widgetPayload = {
    profile_name: @json(\App\Support\ActiveProfile::current()->name),
    status: @json(($entryToday && $entryToday->time_in && !$entryToday->time_out) ? 'clocked_in' : 'clocked_out'),
    status_label: @json(($entryToday && $entryToday->time_in && !$entryToday->time_out) ? 'Clocked In' : 'Clocked Out'),
    total_hours: @json(round($totalMinutes / 60, 1)),
    total_days: @json($totalDays),
    clocked_in_at: @json($entryToday?->time_in ? $entryToday->time_in->timezone($tz)->format('h:i A') : null),
    updated_at: @json(now()->toIso8601String())
};

const criticalUnderHoursPayload = @json($criticalUnderHoursPayload);

function syncHomeWidget() {
    const payloadJson = JSON.stringify(widgetPayload);

    if (window.AndroidBridge && typeof window.AndroidBridge.syncHomeWidget === 'function') {
        window.AndroidBridge.syncHomeWidget(payloadJson);
        return;
    }

    if (window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.syncHomeWidget) {
        window.webkit.messageHandlers.syncHomeWidget.postMessage(payloadJson);
    }
}

function syncMissingEntriesReminder() {
    if (window.AndroidBridge && typeof window.AndroidBridge.syncMissingEntriesReminder === 'function') {
        window.AndroidBridge.syncMissingEntriesReminder(JSON.stringify({
            enabled: @json(\App\Models\Setting::get('missing_entries_reminder_enabled', '1') === '1'),
            timezone: @json($tz),
            profile_name: @json(\App\Support\ActiveProfile::current()->name),
            hour: 9,
            minute: 0,
            skip_today: @json((bool) ($entryToday?->time_in)),
        }));
    }
}

function syncCriticalUnderHoursAlert() {
    const payloadJson = JSON.stringify(criticalUnderHoursPayload);

    if (window.AndroidBridge && typeof window.AndroidBridge.syncCriticalUnderHoursAlert === 'function') {
        window.AndroidBridge.syncCriticalUnderHoursAlert(payloadJson);
        return;
    }

    if (window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.syncCriticalUnderHoursAlert) {
        window.webkit.messageHandlers.syncCriticalUnderHoursAlert.postMessage(payloadJson);
    }
}

function isIOSPullSupported() {
    const ua = window.navigator.userAgent || '';

    return /iPad|iPhone|iPod/.test(ua) || (ua.includes('Macintosh') && 'ontouchend' in document);
}

function updatePullUI(distance) {
    const clamped = Math.min(Math.max(distance, 0), PULL_MAX);
    const offset = Math.round(clamped * 0.45);

    pullIndicator.classList.add('visible');
    pullIndicator.style.transform = 'translate(-50%, calc(-110% + ' + offset + 'px))';
    pullText.innerText = clamped >= PULL_THRESHOLD ? 'Release to refresh' : 'Pull to refresh';

    if (appMain) {
        appMain.classList.remove('pull-refresh-reset');
        appMain.classList.add('pull-refresh-active');
        appMain.style.transform = 'translateY(' + offset + 'px)';
    }
}

function resetPullUI() {
    pullToRefresh.active = false;
    pullToRefresh.armed = false;
    pullToRefresh.distance = 0;

    pullIndicator.classList.remove('visible', 'refreshing');
    pullIndicator.style.transform = 'translate(-50%, -110%)';
    pullText.innerText = 'Pull to refresh';

    if (appMain) {
        appMain.classList.remove('pull-refresh-active');
        appMain.classList.add('pull-refresh-reset');
        appMain.style.transform = 'translateY(0)';

        window.setTimeout(function() {
            appMain.classList.remove('pull-refresh-reset');
        }, 220);
    }
}

function startRefresh() {
    if (pullToRefresh.refreshing) {
        return;
    }

    pullToRefresh.refreshing = true;
    pullIndicator.classList.add('visible', 'refreshing');
    pullIndicator.style.transform = 'translate(-50%, calc(-110% + 56px))';
    pullText.innerText = 'Refreshing...';

    if (appMain) {
        appMain.classList.remove('pull-refresh-active');
        appMain.classList.add('pull-refresh-reset');
        appMain.style.transform = 'translateY(42px)';
    }

    window.setTimeout(function() {
        window.location.reload();
    }, 120);
}

function handlePullStart(event) {
    if (!pullToRefresh.enabled || pullToRefresh.refreshing) {
        return;
    }

    if (event.touches.length !== 1) {
        return;
    }

    if (document.querySelector('.modal-overlay.visible')) {
        return;
    }

    if (appMain && appMain.scrollTop > 0) {
        return;
    }

    pullToRefresh.active = true;
    pullToRefresh.startY = event.touches[0].clientY;
    pullToRefresh.distance = 0;
}

function handlePullMove(event) {
    if (!pullToRefresh.active || pullToRefresh.refreshing) {
        return;
    }

    const currentY = event.touches[0].clientY;
    const delta = currentY - pullToRefresh.startY;

    if (delta <= 0) {
        return;
    }

    if (appMain && appMain.scrollTop > 0) {
        resetPullUI();
        return;
    }

    pullToRefresh.distance = delta;
    pullToRefresh.armed = delta >= PULL_THRESHOLD;
    updatePullUI(delta);

    event.preventDefault();
}

function handlePullEnd() {
    if (!pullToRefresh.active || pullToRefresh.refreshing) {
        return;
    }

    if (pullToRefresh.armed) {
        startRefresh();
        return;
    }

    resetPullUI();
}

function registerPullToRefresh() {
    pullToRefresh.enabled = isIOSPullSupported() && !!appMain && !!pullIndicator && !!pullText;

    if (!pullToRefresh.enabled) {
        return;
    }

    window.addEventListener('touchstart', handlePullStart, { passive: true });
    window.addEventListener('touchmove', handlePullMove, { passive: false });
    window.addEventListener('touchend', handlePullEnd, { passive: true });
    window.addEventListener('touchcancel', handlePullEnd, { passive: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        syncHomeWidget();
        syncMissingEntriesReminder();
        syncCriticalUnderHoursAlert();
        registerPullToRefresh();
    });
} else {
    syncHomeWidget();
    syncMissingEntriesReminder();
    syncCriticalUnderHoursAlert();
    registerPullToRefresh();
}

function submitClock(url) {
    currentUrl = url;
    const isClockIn = url.indexOf('clock-in') !== -1;
    currentHapticType = isClockIn ? 'clock_in_success' : 'clock_out_completion';
    const modal = document.getElementById('confirmModal');
    const title = document.getElementById('modalTitle');
    const body = document.getElementById('modalBody');
    const confirmBtn = document.getElementById('modalConfirmBtn');

    title.innerText = isClockIn ? 'Clock In' : 'Clock Out';
    body.innerText = isClockIn
        ? 'Are you sure you want to clock in? This will record your current start time.'
        : 'Are you sure you want to clock out? This will calculate and record your total hours for today.';

    confirmBtn.className = isClockIn ? 'btn-modal confirm' : 'btn-modal confirm danger';
    confirmBtn.onclick = executeSubmit;

    modal.classList.add('visible');

    if (typeof window.pushModalHistory === 'function') {
        window.pushModalHistory('confirmModal');
    }
}

function closeModal(fromBack) {
    document.getElementById('confirmModal').classList.remove('visible');

    if (!fromBack && typeof window.popModalHistory === 'function') {
        window.popModalHistory('confirmModal');
    }
}

function executeSubmit() {
    closeModal();
    const btn = document.getElementById('clockBtn');
    if (btn) btn.disabled = true;

    fetch(currentUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'text/html'
        },
        body: '_token={{ csrf_token() }}'
    }).then(function() {
        if (typeof window.triggerHapticFeedback === 'function' && currentHapticType) {
            window.triggerHapticFeedback(currentHapticType);
        }

        window.location.href = '{{ route('dashboard', [], false) }}';
    }).catch(function() {
        window.location.href = '{{ route('dashboard', [], false) }}';
    });
}
</script>

@endsection

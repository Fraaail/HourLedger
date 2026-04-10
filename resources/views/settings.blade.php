@extends('layouts.app')

@section('content')

<div id="statusNotification" class="notification success-notification" style="display: none; opacity: 0; transition: opacity 0.3s ease;">
    <div class="notification-title" id="statusMessage">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span id="statusText">Setting updated.</span>
    </div>
</div>

@if(session('success'))
<div class="notification success-notification" id="sessionNotification">
    <div class="notification-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        {{ session('success') }}
    </div>
</div>
<script>setTimeout(() => document.getElementById('sessionNotification')?.remove(), 3000);</script>
@endif

@if($errors->any())
<div class="notification" id="errorNotification">
    <div class="notification-title">
        <span>{{ $errors->first() }}</span>
    </div>
</div>
<script>setTimeout(() => document.getElementById('errorNotification')?.remove(), 4000);</script>
@endif

<div class="settings-section">
    <h2 class="settings-heading">Theme</h2>
    <p class="settings-description">Choose your preferred appearance.</p>

    <form id="themeForm">
        <div class="settings-field">
            <label for="theme" class="settings-label">App Theme</label>
            <select name="theme" id="theme" class="settings-select" onchange="submitTheme(this.value)">
                <option value="dark" {{ $theme === 'dark' ? 'selected' : '' }}>Dark</option>
                <option value="light" {{ $theme === 'light' ? 'selected' : '' }}>Light</option>
                <option value="system" {{ $theme === 'system' ? 'selected' : '' }}>System (Auto)</option>
            </select>
        </div>
    </form>
</div>

<div class="settings-section">
    <h2 class="settings-heading">Timezone</h2>
    <p class="settings-description">Select your local timezone. All timestamps will be displayed in this timezone.</p>

    <form id="timezoneForm">
        <div class="settings-field">
            <label for="timezone" class="settings-label">Timezone</label>
            <select name="timezone" id="timezone" class="settings-select" onchange="submitTimezone(this.value)">
                @foreach($timezones as $tz)
                    <option value="{{ $tz }}" {{ $timezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<div class="settings-section">
    <h2 class="settings-heading">Missing Entry Reminders</h2>
    <p class="settings-description">Send a weekday local reminder at 9:00 AM if you have not clocked in for today.</p>

    <form id="missingEntriesReminderForm">
        <div class="settings-field" style="padding: 0.75rem; border-radius: 0.75rem; background: var(--bg-secondary);">
            <label for="missing_entries_reminder_enabled" class="toggle-container" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; width: 100%;">
                <span class="settings-label" style="margin-bottom: 0;">Enable Missing Entry Reminder</span>
                <input
                    type="checkbox"
                    name="missing_entries_reminder_enabled"
                    id="missing_entries_reminder_enabled"
                    style="width: 1.25rem; height: 1.25rem;"
                    {{ $missingEntriesReminderEnabled ? 'checked' : '' }}
                    onchange="submitMissingEntriesReminder(this.checked)"
                >
            </label>
        </div>
    </form>
</div>

<div class="settings-section">
    <h2 class="settings-heading">End-of-Day Under-Hours Alerts</h2>
    <p class="settings-description">Schedule an end-of-day alert when your rendered time is still below your daily target.</p>

    <form id="criticalAlertForm">
        <div class="settings-field" style="padding: 0.75rem; border-radius: 0.75rem; background: var(--bg-secondary);">
            <label for="critical_alerts_enabled" class="toggle-container" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; width: 100%;">
                <span class="settings-label" style="margin-bottom: 0;">Enable Under-Hours Alert</span>
                <input
                    type="checkbox"
                    name="critical_alerts_enabled"
                    id="critical_alerts_enabled"
                    style="width: 1.25rem; height: 1.25rem;"
                    {{ $criticalAlertsEnabled ? 'checked' : '' }}
                    onchange="submitCriticalAlerts()"
                >
            </label>
        </div>

        <div class="settings-field" style="margin-top: 0.75rem;">
            <label for="critical_alert_required_hours" class="settings-label">Daily Target Hours</label>
            <input
                type="number"
                id="critical_alert_required_hours"
                class="settings-select"
                min="1"
                max="16"
                step="0.5"
                value="{{ number_format($criticalAlertRequiredMinutes / 60, 1, '.', '') }}"
                onchange="submitCriticalAlerts()"
            >
        </div>

        <div class="settings-field" style="margin-top: 0.75rem;">
            <label for="critical_alert_time" class="settings-label">Reminder Time</label>
            <input
                type="time"
                id="critical_alert_time"
                class="settings-select"
                value="{{ sprintf('%02d:%02d', $criticalAlertHour, $criticalAlertMinute) }}"
                onchange="submitCriticalAlerts()"
            >
        </div>

        <p class="settings-description" style="margin-top: 0.75rem;">
            iOS Critical Alert local scheduling is active through the native bridge.
            Production App Store rollout still requires Apple critical-alert entitlement approval and final code-signing setup.
        </p>
    </form>
</div>

<div class="settings-section">
    <h2 class="settings-heading">Current Time</h2>
    <p class="settings-description">Based on your selected timezone.</p>
    <div class="metric-card" style="margin-top: 1rem;">
        <h3 id="currentTimeLabel">{{ $timezone }}</h3>
        <div class="value" id="currentTimeValue">{{ now()->timezone($timezone)->format('h:i A') }}</div>
    </div>
</div>

<div class="settings-section">
    <h2 class="settings-heading">Export &amp; Share</h2>
    <p class="settings-description">Generate your profile timesheet as CSV and share it with supervisors or coordinators.</p>

    <button type="button" class="settings-btn" onclick="exportAndShareTimesheet()">Export &amp; Share CSV</button>
</div>

<script>
let criticalUnderHoursBasePayload = @json($criticalUnderHoursPayload);

function showStatus(message, isError = false) {
    const notify = document.getElementById('statusNotification');
    const text = document.getElementById('statusText');

    notify.classList.toggle('success-notification', !isError);
    notify.classList.toggle('error-notification', isError);

    text.innerText = message;
    notify.style.display = 'block';
    setTimeout(() => notify.style.opacity = '1', 10);

    setTimeout(() => {
        notify.style.opacity = '0';
        setTimeout(() => notify.style.display = 'none', 300);
    }, 3000);
}

function submitTheme(theme) {
    // Immediate visual feedback
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: theme } }));

    fetch('{{ route('settings.theme', [], false) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: '_token={{ csrf_token() }}&theme=' + encodeURIComponent(theme)
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            showStatus(data.message);
        }
    }).catch(error => {
        console.error('Theme update failed:', error);
    });
}

function submitTimezone(tz) {
    fetch('{{ route('settings.timezone', [], false) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: '_token={{ csrf_token() }}&timezone=' + encodeURIComponent(tz)
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('currentTimeLabel').innerText = data.timezone;
            document.getElementById('currentTimeValue').innerText = data.currentTime;
            showStatus(data.message);

            const reminderEnabled = document.getElementById('missing_entries_reminder_enabled')?.checked ?? true;
            syncMissingEntriesReminder({
                enabled: reminderEnabled,
                timezone: data.timezone,
                profile_name: @json(\App\Support\ActiveProfile::current()->name),
                hour: 9,
                minute: 0,
                skip_today: false,
            });

            syncCriticalUnderHoursAlert(buildCriticalUnderHoursPayload(data.timezone));
        }
    }).catch(error => {
        console.error('Timezone update failed:', error);
    });
}

function syncMissingEntriesReminder(payload) {
    if (window.AndroidBridge && typeof window.AndroidBridge.syncMissingEntriesReminder === 'function') {
        window.AndroidBridge.syncMissingEntriesReminder(JSON.stringify(payload));
    }
}

function syncCriticalUnderHoursAlert(payload) {
    const payloadJson = JSON.stringify(payload);

    if (window.AndroidBridge && typeof window.AndroidBridge.syncCriticalUnderHoursAlert === 'function') {
        window.AndroidBridge.syncCriticalUnderHoursAlert(payloadJson);
        return;
    }

    if (window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.syncCriticalUnderHoursAlert) {
        window.webkit.messageHandlers.syncCriticalUnderHoursAlert.postMessage(payloadJson);
    }
}

function clampNumber(value, min, max, fallback) {
    const parsed = Number(value);

    if (!Number.isFinite(parsed)) {
        return fallback;
    }

    return Math.min(max, Math.max(min, parsed));
}

function readCriticalAlertFormState() {
    const enabled = document.getElementById('critical_alerts_enabled')?.checked ?? false;
    const requiredHours = clampNumber(
        document.getElementById('critical_alert_required_hours')?.value,
        1,
        16,
        8
    );
    const requiredMinutes = Math.round(requiredHours * 60);

    const timeValue = document.getElementById('critical_alert_time')?.value || '18:00';
    const parts = timeValue.split(':');
    const hour = clampNumber(parts[0], 0, 23, 18);
    const minute = clampNumber(parts[1], 0, 59, 0);

    return {
        enabled: enabled,
        required_minutes: requiredMinutes,
        hour: hour,
        minute: minute,
    };
}

function buildCriticalUnderHoursPayload(timezoneOverride = null) {
    const formState = readCriticalAlertFormState();
    const timezone = timezoneOverride || criticalUnderHoursBasePayload.timezone;
    const todayTotalMinutes = Number(criticalUnderHoursBasePayload.today_total_minutes || 0);

    return {
        enabled: formState.enabled,
        timezone: timezone,
        profile_name: criticalUnderHoursBasePayload.profile_name,
        required_minutes: formState.required_minutes,
        today_total_minutes: todayTotalMinutes,
        under_hours: todayTotalMinutes < formState.required_minutes,
        hour: formState.hour,
        minute: formState.minute,
    };
}

function extractFilename(dispositionHeader) {
    if (!dispositionHeader) {
        return 'timesheet.csv';
    }

    const utfMatch = dispositionHeader.match(/filename\*=UTF-8''([^;]+)/i);
    if (utfMatch && utfMatch[1]) {
        return decodeURIComponent(utfMatch[1]);
    }

    const plainMatch = dispositionHeader.match(/filename="?([^\";]+)"?/i);
    if (plainMatch && plainMatch[1]) {
        return plainMatch[1];
    }

    return 'timesheet.csv';
}

async function exportAndShareTimesheet() {
    try {
        const response = await fetch('{{ route('export.timesheet.csv', [], false) }}', {
            method: 'GET',
            headers: {
                'Accept': 'text/csv'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to generate timesheet file.');
        }

        const blob = await response.blob();
        const filename = extractFilename(response.headers.get('content-disposition'));
        const file = new File([blob], filename, { type: 'text/csv' });

        if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                title: 'HourLedger Timesheet',
                text: 'Timesheet export from HourLedger.',
                files: [file],
            });
            showStatus('Timesheet ready to share.');

            return;
        }

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        showStatus('Timesheet exported.');
    } catch (error) {
        if (error && error.name === 'AbortError') {
            return;
        }

        console.error('Timesheet export failed:', error);
        showStatus('Failed to export timesheet.', true);
    }
}

function submitMissingEntriesReminder(enabled) {
    fetch('{{ route('settings.missing_entries_reminder', [], false) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: '_token={{ csrf_token() }}&enabled=' + (enabled ? '1' : '0')
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            showStatus(data.message);
            syncMissingEntriesReminder(data.payload);
            return;
        }

        throw new Error('Reminder update failed.');
    }).catch(error => {
        const toggle = document.getElementById('missing_entries_reminder_enabled');
        if (toggle) {
            toggle.checked = !enabled;
        }

        console.error('Missing entries reminder update failed:', error);
        showStatus('Failed to update reminder setting.', true);
    });
}

function submitCriticalAlerts() {
    const formState = readCriticalAlertFormState();

    fetch('{{ route('settings.critical_alerts', [], false) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: '_token={{ csrf_token() }}'
            + '&enabled=' + (formState.enabled ? '1' : '0')
            + '&required_minutes=' + encodeURIComponent(formState.required_minutes)
            + '&hour=' + encodeURIComponent(formState.hour)
            + '&minute=' + encodeURIComponent(formState.minute)
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            criticalUnderHoursBasePayload = data.payload;
            showStatus(data.message);
            syncCriticalUnderHoursAlert(data.payload);
            return;
        }

        throw new Error('Critical alert update failed.');
    }).catch(error => {
        console.error('Critical alert update failed:', error);
        showStatus('Failed to update under-hours alerts.', true);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        syncMissingEntriesReminder({
            enabled: @json($missingEntriesReminderEnabled),
            timezone: @json($timezone),
            profile_name: @json(\App\Support\ActiveProfile::current()->name),
            hour: 9,
            minute: 0,
            skip_today: false,
        });

        syncCriticalUnderHoursAlert(buildCriticalUnderHoursPayload());
    });
} else {
    syncMissingEntriesReminder({
        enabled: @json($missingEntriesReminderEnabled),
        timezone: @json($timezone),
        profile_name: @json(\App\Support\ActiveProfile::current()->name),
        hour: 9,
        minute: 0,
        skip_today: false,
    });

    syncCriticalUnderHoursAlert(buildCriticalUnderHoursPayload());
}
</script>

@endsection

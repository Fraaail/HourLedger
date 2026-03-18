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
    <h2 class="settings-heading">Current Time</h2>
    <p class="settings-description">Based on your selected timezone.</p>
    <div class="metric-card" style="margin-top: 1rem;">
        <h3 id="currentTimeLabel">{{ $timezone }}</h3>
        <div class="value" id="currentTimeValue">{{ now()->timezone($timezone)->format('h:i A') }}</div>
    </div>
</div>

<script>
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
        }
    }).catch(error => {
        console.error('Timezone update failed:', error);
    });
}
</script>

@endsection

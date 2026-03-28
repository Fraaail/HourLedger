@extends('layouts.app')

@section('content')

<div id="statusNotification" class="notification success-notification" style="display: none; opacity: 0; transition: opacity 0.3s ease;">
    <div class="notification-title" id="statusMessage">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span id="statusText">Profile updated.</span>
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
    <h2 class="settings-heading">Profiles</h2>
    <p class="settings-description">Create separate profiles so multiple users can use this device independently.</p>

    <form id="profileForm" onsubmit="return createProfile(event)">
        <div class="settings-field">
            <label for="profile_name" class="settings-label">New Profile Name</label>
            <input type="text" name="profile_name" id="profile_name" class="settings-input" maxlength="60" placeholder="e.g. John Doe" required>
        </div>
        <button type="submit" class="settings-btn">Create Profile</button>
    </form>

    <button type="button" class="view-all-profiles-btn" id="toggleProfilesBtn" onclick="toggleProfileList()">
        <span>View All Profiles</span>
        <svg id="toggleIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.3s ease;">
            <path d="M6 9l6 6 6-6"></path>
        </svg>
    </button>

    <div class="profile-management-list hidden" id="profileManagementList">
        @foreach($managedProfiles as $profile)
            <div class="profile-management-item {{ $profile->is_archived ? 'is-archived' : '' }}">
                <div class="profile-management-main">
                    <input
                        type="text"
                        class="settings-input profile-name-input"
                        id="profile_name_{{ $profile->id }}"
                        value="{{ $profile->name }}"
                        maxlength="60"
                    >
                    <div class="profile-management-badges">
                        @if($profile->is_default)
                            <span class="profile-badge">Default</span>
                        @endif
                        @if(isset($activeProfile) && $activeProfile->id === $profile->id)
                            <span class="profile-badge">Active</span>
                        @endif
                        @if($profile->is_archived)
                            <span class="profile-badge archived">Archived</span>
                        @endif
                    </div>
                </div>

                @if(\Native\Mobile\Facades\System::isMobile())
                <div class="profile-management-settings" style="margin-top: 0.75rem; padding: 0.75rem; border-radius: 0.75rem; background: var(--bg-secondary);">
                    <label class="toggle-container" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; width: 100%;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span class="settings-label" style="margin-bottom: 0;">Biometric Authentication</span>
                        </div>
                        <input type="checkbox" id="biometric_auth_{{ $profile->id }}" {{ $profile->biometric_auth ? 'checked' : '' }} style="width: 1.25rem; height: 1.25rem;">
                    </label>
                </div>
                @endif

                <div class="profile-management-actions">
                    <button type="button" class="settings-btn profile-action-btn" onclick="updateProfile({{ $profile->id }})">
                        Save
                    </button>
                    @if(! $profile->is_default && ! $profile->is_archived)
                        <button type="button" class="settings-btn profile-action-btn muted" onclick="archiveProfile({{ $profile->id }}, '{{ addslashes($profile->name) }}')">
                            Archive
                        </button>
                    @endif
                    @if(! $profile->is_default && $profile->is_archived)
                        <button type="button" class="settings-btn profile-action-btn muted" onclick="unarchiveProfile({{ $profile->id }}, '{{ addslashes($profile->name) }}')">
                            Unarchive
                        </button>
                    @endif
                    @if(! $profile->is_default)
                        <button type="button" class="settings-btn profile-action-btn danger" onclick="deleteProfile({{ $profile->id }}, '{{ addslashes($profile->name) }}')">
                            Delete
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<div id="profileConfirmOverlay" class="modal-overlay profile-confirmation-modal" aria-hidden="true">
    <div class="modal-content">
        <div class="profile-confirm-icon" id="profileConfirmIcon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 12l2 2 4-4"></path>
                <path d="M21 12c0 4.97-4.03 9-9 9S3 16.97 3 12 7.03 3 12 3s9 4.03 9 9z"></path>
            </svg>
        </div>
        <h3 class="modal-title" id="profileConfirmTitle">Confirm action</h3>
        <p class="modal-body" id="profileConfirmBody">Please confirm this profile action.</p>
        <div class="modal-actions">
            <button type="button" class="btn-modal confirm" id="profileConfirmSubmit">Continue</button>
            <button type="button" class="btn-modal cancel" id="profileConfirmCancel">Cancel</button>
        </div>
    </div>
</div>

<script>
const profileUpdateUrlTemplate = @json(route('profiles.update', ['profile' => '__PROFILE__'], false));
const profileArchiveUrlTemplate = @json(route('profiles.archive', ['profile' => '__PROFILE__'], false));
const profileUnarchiveUrlTemplate = @json(route('profiles.unarchive', ['profile' => '__PROFILE__'], false));
const profileDestroyUrlTemplate = @json(route('profiles.destroy', ['profile' => '__PROFILE__'], false));

const profileConfirmState = {
    callback: null,
};

function profileConfirmElements() {
    return {
        overlay: document.getElementById('profileConfirmOverlay'),
        icon: document.getElementById('profileConfirmIcon'),
        title: document.getElementById('profileConfirmTitle'),
        body: document.getElementById('profileConfirmBody'),
        submit: document.getElementById('profileConfirmSubmit'),
        cancel: document.getElementById('profileConfirmCancel'),
    };
}

function closeProfileConfirm() {
    const elements = profileConfirmElements();

    elements.overlay.classList.remove('visible');
    elements.overlay.setAttribute('aria-hidden', 'true');
    profileConfirmState.callback = null;
}

function openProfileConfirm(options) {
    const elements = profileConfirmElements();
    const danger = !!options.danger;

    elements.title.innerText = options.title;
    elements.body.innerText = options.body;
    elements.submit.innerText = options.confirmLabel || 'Confirm';
    elements.submit.classList.toggle('danger', danger);
    elements.icon.classList.toggle('danger', danger);

    profileConfirmState.callback = options.onConfirm;
    elements.overlay.classList.add('visible');
    elements.overlay.setAttribute('aria-hidden', 'false');
}

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

function buildProfileUrl(template, profileId) {
    return template.replace('__PROFILE__', encodeURIComponent(String(profileId)));
}

function sendProfileRequest(url, method, body) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: body,
    }).then(async response => {
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Profile action failed.');
        }

        return data;
    });
}

function toggleProfileList() {
    const list = document.getElementById('profileManagementList');
    const btn = document.getElementById('toggleProfilesBtn');
    const icon = document.getElementById('toggleIcon');
    const text = btn.querySelector('span');

    const isHidden = list.classList.toggle('hidden');

    if (isHidden) {
        text.innerText = 'View All Profiles';
        icon.style.transform = 'rotate(0deg)';
    } else {
        text.innerText = 'Hide All Profiles';
        icon.style.transform = 'rotate(180deg)';
    }
}

function createProfile(e) {
    e.preventDefault();

    const input = document.getElementById('profile_name');
    const name = input.value.trim();
    if (!name) {
        showStatus('Profile name is required.', true);
        return false;
    }

    openProfileConfirm({
        title: 'Create Profile',
        body: 'Create a new profile named "' + name + '"?',
        confirmLabel: 'Create',
        onConfirm: () => {
            fetch('{{ route('profiles.store', [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: '_token={{ csrf_token() }}&name=' + encodeURIComponent(name)
            }).then(async response => {
                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Profile creation failed.');
                }

                return data;
            }).then(data => {
                showStatus(data.message);
                window.location.reload();
            }).catch(error => {
                showStatus(error.message || 'Profile creation failed.', true);
            });
        },
    });

    return false;
}

function updateProfile(profileId) {
    const input = document.getElementById('profile_name_' + profileId);
    const name = (input?.value || '').trim();

    if (!name) {
        showStatus('Profile name is required.', true);
        return;
    }

    const biometricCheckbox = document.getElementById('biometric_auth_' + profileId);
    const biometricAuth = biometricCheckbox ? (biometricCheckbox.checked ? '1' : '0') : '0';

    openProfileConfirm({
        title: 'Save Profile Changes',
        body: 'Save this profile as "' + name + '"?',
        confirmLabel: 'Save',
        onConfirm: () => {
            sendProfileRequest(
                buildProfileUrl(profileUpdateUrlTemplate, profileId),
                'POST',
                '_token={{ csrf_token() }}&_method=PATCH&name=' + encodeURIComponent(name) + '&biometric_auth=' + biometricAuth
            ).then(data => {
                showStatus(data.message);
                window.location.reload();
            }).catch(error => {
                showStatus(error.message, true);
            });
        },
    });
}

function archiveProfile(profileId, profileName) {
    openProfileConfirm({
        title: 'Archive Profile',
        body: 'Archive "' + profileName + '"? It will be hidden from the profile switcher.',
        confirmLabel: 'Archive',
        danger: true,
        onConfirm: () => {
            sendProfileRequest(
                buildProfileUrl(profileArchiveUrlTemplate, profileId),
                'POST',
                '_token={{ csrf_token() }}'
            ).then(data => {
                showStatus(data.message);
                window.location.reload();
            }).catch(error => {
                showStatus(error.message, true);
            });
        },
    });
}

function unarchiveProfile(profileId, profileName) {
    openProfileConfirm({
        title: 'Unarchive Profile',
        body: 'Unarchive "' + profileName + '" and make it available again?',
        confirmLabel: 'Unarchive',
        onConfirm: () => {
            sendProfileRequest(
                buildProfileUrl(profileUnarchiveUrlTemplate, profileId),
                'POST',
                '_token={{ csrf_token() }}'
            ).then(data => {
                showStatus(data.message);
                window.location.reload();
            }).catch(error => {
                showStatus(error.message, true);
            });
        },
    });
}

function deleteProfile(profileId, profileName) {
    if (typeof window.triggerHapticFeedback === 'function') {
        window.triggerHapticFeedback('deletion_warning');
    }

    openProfileConfirm({
        title: 'Delete Profile',
        body: 'Delete "' + profileName + '" permanently? This cannot be undone.',
        confirmLabel: 'Delete',
        danger: true,
        onConfirm: () => {
            sendProfileRequest(
                buildProfileUrl(profileDestroyUrlTemplate, profileId),
                'POST',
                '_token={{ csrf_token() }}&_method=DELETE'
            ).then(data => {
                showStatus(data.message);
                window.location.reload();
            }).catch(error => {
                showStatus(error.message, true);
            });
        },
    });
}

(function registerProfileConfirmHandlers() {
    const elements = profileConfirmElements();

    elements.cancel.addEventListener('click', closeProfileConfirm);

    elements.overlay.addEventListener('click', function(event) {
        if (event.target === elements.overlay) {
            closeProfileConfirm();
        }
    });

    elements.submit.addEventListener('click', function() {
        if (typeof profileConfirmState.callback === 'function') {
            const action = profileConfirmState.callback;
            closeProfileConfirm();
            action();
            return;
        }

        closeProfileConfirm();
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeProfileConfirm();
        }
    });
})();
</script>
@endsection

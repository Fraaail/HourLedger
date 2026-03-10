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
        <form action="{{ route('time.in') }}" method="POST">
            @csrf
            <button type="submit" class="btn-time in">
                TIME IN
                <span class="helper">{{ now()->timezone($tz)->format('h:i A') }}</span>
            </button>
        </form>
    @elseif($entryToday && !$entryToday->time_out)
        <form action="{{ route('time.out') }}" method="POST">
            @csrf
            <button type="submit" class="btn-time out">
                TIME OUT
                <span class="helper">In at {{ $entryToday->time_in->timezone($tz)->format('h:i A') }}</span>
            </button>
        </form>
    @else
        <button type="button" class="btn-time" disabled>
            COMPLETED
            <span class="helper">{{ number_format($entryToday->total_minutes / 60, 1) }}h today</span>
        </button>
    @endif
</div>

@endsection

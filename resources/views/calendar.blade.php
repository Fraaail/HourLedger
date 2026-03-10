@extends('layouts.app')

@section('content')

@php
    $now = Carbon\Carbon::now($tz);
    $daysInMonth = $now->daysInMonth;
    $firstDayOfMonth = $now->copy()->firstOfMonth()->dayOfWeek;
@endphp

<div class="calendar-controls">
    <span>{{ $now->format('F Y') }}</span>
</div>

<div class="calendar-grid">
    <div class="calendar-header">Sun</div>
    <div class="calendar-header">Mon</div>
    <div class="calendar-header">Tue</div>
    <div class="calendar-header">Wed</div>
    <div class="calendar-header">Thu</div>
    <div class="calendar-header">Fri</div>
    <div class="calendar-header">Sat</div>

    @for($i = 0; $i < $firstDayOfMonth; $i++)
        <div class="calendar-cell empty"></div>
    @endfor

    @for($day = 1; $day <= $daysInMonth; $day++)
        @php
            $dateStr = $now->copy()->setDay($day)->toDateString();
            $formattedDate = $now->copy()->setDay($day)->format('M d, Y');
            $entry = $entries->get($dateStr);

            $classes = '';
            $inStr = 'N/A';
            $outStr = 'N/A';
            $durationStr = '0.0h';

            if ($entry) {
                if ($entry->time_out) {
                    $classes = 'rendered';
                    $inStr = $entry->time_in->timezone($tz)->format('h:i A');
                    $outStr = $entry->time_out->timezone($tz)->format('h:i A');
                    $durationStr = number_format($entry->total_minutes / 60, 1) . 'h';
                } elseif ($entry->time_in) {
                    $classes = 'partial';
                    $inStr = $entry->time_in->timezone($tz)->format('h:i A');
                    $outStr = 'Running';
                }
            }
        @endphp
        <div class="calendar-cell {{ $classes }}" onclick="showDetails('{{ $formattedDate }}', '{{ $inStr }}', '{{ $outStr }}', '{{ $durationStr }}')">
            {{ $day }}
        </div>
    @endfor
</div>

<div class="calendar-details" id="calendarDetails">
    <h3 style="margin-bottom: 1rem; color: var(--text-primary); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;" id="detailDate">Date</h3>
    <div class="details-row">
        <span style="color: var(--text-secondary)">Time In</span>
        <span style="font-weight: 700;" id="detailIn">--</span>
    </div>
    <div class="details-row">
        <span style="color: var(--text-secondary)">Time Out</span>
        <span style="font-weight: 700;" id="detailOut">--</span>
    </div>
    <div class="details-row">
        <span style="color: var(--text-secondary)">Duration</span>
        <span style="font-weight: 700; color: var(--accent-color)" id="detailTotal">--</span>
    </div>
</div>

<script>
    function showDetails(date, timeIn, timeOut, total) {
        const detailsBox = document.getElementById('calendarDetails');

        detailsBox.classList.remove('visible');
        void detailsBox.offsetWidth; // reflow

        document.getElementById('detailDate').innerText = date;
        document.getElementById('detailIn').innerText = timeIn;
        document.getElementById('detailOut').innerText = timeOut;
        document.getElementById('detailTotal').innerText = total;

        detailsBox.classList.add('visible');
    }
</script>

@endsection

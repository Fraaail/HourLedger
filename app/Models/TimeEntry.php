<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'time_in',
        'time_out',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time_in' => 'datetime',
            'time_out' => 'datetime',
        ];
    }

    /**
     * Calculate rendered hours for this entry.
     */
    public function getRenderedHoursAttribute(): float
    {
        if (! $this->time_in || ! $this->time_out) {
            return 0;
        }

        return round($this->time_in->diffInMinutes($this->time_out) / 60, 2);
    }

    /**
     * Check if this entry is complete (has both time_in and time_out).
     */
    public function getIsCompleteAttribute(): bool
    {
        return $this->time_in !== null && $this->time_out !== null;
    }

    /**
     * Get status: 'complete', 'incomplete', or 'missing'.
     */
    public function getStatusAttribute(): string
    {
        if ($this->time_in && $this->time_out) {
            return 'complete';
        }

        if ($this->time_in) {
            return 'incomplete';
        }

        return 'missing';
    }

    /**
     * Get formatted time-in string.
     */
    public function getFormattedTimeInAttribute(): ?string
    {
        return $this->time_in?->format('h:i A');
    }

    /**
     * Get formatted time-out string.
     */
    public function getFormattedTimeOutAttribute(): ?string
    {
        return $this->time_out?->format('h:i A');
    }

    /**
     * Scope: entries for a given month.
     */
    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    /**
     * Scope: entries for the current week.
     */
    public function scopeCurrentWeek($query)
    {
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        return $query->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()]);
    }

    /**
     * Scope: only complete entries.
     */
    public function scopeComplete($query)
    {
        return $query->whereNotNull('time_in')->whereNotNull('time_out');
    }
}

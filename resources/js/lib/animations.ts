/**
 * CSS class helpers for HourLedger animations.
 */

export const animations = {
    fadeInUp: 'animate-fade-in-up',
    fadeIn: 'animate-fade-in',
    slideUp: 'animate-slide-up',
    scaleIn: 'animate-scale-in',
    pulseSoft: 'animate-pulse-soft',
} as const;

export const stagger = (index: number): string => {
    const delays = ['stagger-1', 'stagger-2', 'stagger-3', 'stagger-4', 'stagger-5'];
    return delays[Math.min(index, delays.length - 1)] ?? 'stagger-1';
};

export const tapEffect = 'tap-effect';

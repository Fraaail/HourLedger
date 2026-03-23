<?php

namespace App\Listeners;

use Native\Mobile\Events\Biometric\Completed;

class HandleBiometricCompletion
{
    public function handle(Completed $event): void
    {
        if ($event->success && $event->id) {
            session()->flash('_native_biometric_success_' . $event->id, true);
        }
    }
}

<?php

namespace App\Providers;

use App\Listeners\HandleBiometricCompletion;
use App\Models\Profile;
use App\Support\ActiveProfile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Native\Mobile\Events\Biometric\Completed;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            Completed::class,
            HandleBiometricCompletion::class
        );

        View::composer('layouts.app', function ($view): void {
            if (! Schema::hasTable('profiles')) {
                return;
            }

            $activeProfile = ActiveProfile::current();

            $view->with('profiles', Profile::ordered());
            $view->with('activeProfile', $activeProfile);
        });
    }
}

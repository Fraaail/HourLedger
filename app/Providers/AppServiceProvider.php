<?php

namespace App\Providers;

use App\Models\Profile;
use App\Support\ActiveProfile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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

<?php

namespace App\Providers;

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
        View::composer('components.navbar', function ($view): void {
            $user = auth()->user();

            $view->with(
                'navbarUnreadNotificationsCount',
                $user ? $user->unreadNotificationItems()->count() : 0
            );
        });
    }
}

<?php

namespace App\Providers;

use App\Models\Notification;
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

            $recent = collect();
            if ($user) {
                $recent = Notification::query()
                    ->where('user_id', $user->id)
                    ->with('related')
                    ->latest()
                    ->limit(12)
                    ->get();
            }

            $view->with([
                'navbarUnreadNotificationsCount' => $user ? $user->unreadNotificationItems()->count() : 0,
                'navbarRecentNotifications' => $recent,
            ]);
        });
    }
}

<?php

namespace App\Providers;

use App\Listeners\RecordSpatiePermissionActivity;
use App\Models\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Events\PermissionAttached;
use Spatie\Permission\Events\PermissionDetached;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;

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
        Event::listen(RoleAttached::class, [RecordSpatiePermissionActivity::class, 'handleRoleAttached']);
        Event::listen(RoleDetached::class, [RecordSpatiePermissionActivity::class, 'handleRoleDetached']);
        Event::listen(PermissionAttached::class, [RecordSpatiePermissionActivity::class, 'handlePermissionAttached']);
        Event::listen(PermissionDetached::class, [RecordSpatiePermissionActivity::class, 'handlePermissionDetached']);

        View::composer('components.navbar', function ($view): void {
            $user = auth()->user();

            $recent = collect();
            if ($user) {
                $recent = Notification::query()
                    ->where('user_id', $user->id)
                    ->with('related')
                    ->orderBy('is_read')
                    ->orderByDesc('created_at')
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

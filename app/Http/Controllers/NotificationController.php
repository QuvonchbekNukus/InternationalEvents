<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function open(Request $request, Notification $notification): RedirectResponse
    {
        return $this->go($request, $notification);
    }

    public function go(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()?->id, 403);

        $targetUrl = $notification->resolveTargetUrl();

        if (! $targetUrl) {
            return redirect()
                ->to(route('profile.edit').'#profile-notifications')
                ->with('error', "Bildirishnomaga bog'langan ma'lumot topilmadi.");
        }

        return redirect()->to($targetUrl);
    }

    public function markRead(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()?->id, 403);

        $notification->markAsRead();

        return redirect()->back();
    }
}

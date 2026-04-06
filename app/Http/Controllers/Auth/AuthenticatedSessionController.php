<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        if ($user) {
            DB::transaction(function () use ($user, $request): void {
                activity()->withoutLogs(function () use ($user): void {
                    $user->forceFill([
                        'last_login_at' => now(),
                    ])->save();
                });

                activity('system')
                    ->causedBy($user)
                    ->performedOn($user)
                    ->event('login')
                    ->withProperties([
                        'subject_label' => $user->full_name,
                        'subject_type_label' => 'Foydalanuvchi',
                        'causer_name' => $user->full_name,
                        'ip_address' => $request->ip(),
                        'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
                    ])
                    ->log(__('ui.activity_log.events.login'));
            });
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            activity('system')
                ->causedBy($user)
                ->performedOn($user)
                ->event('logout')
                ->withProperties([
                    'subject_label' => $user->full_name,
                    'subject_type_label' => 'Foydalanuvchi',
                    'causer_name' => $user->full_name,
                    'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
                ])
                ->log(__('ui.activity_log.events.logout'));
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogAuthenticatedPageVisit
{
    private const CACHE_TTL_SECONDS = 120;

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (app()->environment('testing')) {
            return;
        }

        if (! $request->user() instanceof User) {
            return;
        }

        if (! $request->isMethod('GET')) {
            return;
        }

        if ($this->shouldSkip($request)) {
            return;
        }

        if ($response->getStatusCode() >= 400) {
            return;
        }

        /** @var User $user */
        $user = $request->user();
        $route = $request->route();
        $routeKey = $route?->getName() ?? $request->path();

        $cacheKey = sprintf('activity_page_visit:%d:%s', $user->id, md5($routeKey));

        if (! Cache::add($cacheKey, true, now()->addSeconds(self::CACHE_TTL_SECONDS))) {
            return;
        }

        activity('system')
            ->causedBy($user)
            ->event('page_visit')
            ->withProperties(array_filter([
                'route' => $routeKey,
                'path' => '/'.ltrim($request->path(), '/'),
                'subject_label' => $routeKey,
                'subject_type_label' => 'Sahifa',
                'causer_name' => $user->full_name,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
            ], fn (mixed $v): bool => $v !== null && $v !== ''))
            ->log(__('ui.activity_log.events.page_visit'));
    }

    protected function shouldSkip(Request $request): bool
    {
        $path = $request->path();

        if (str_starts_with($path, 'livewire')) {
            return true;
        }

        if ($request->expectsJson()) {
            return true;
        }

        return false;
    }
}

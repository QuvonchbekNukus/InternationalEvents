<?php

namespace App\Http\Middleware;

use App\Services\OpportunisticDateReminderDispatcher;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DispatchDateRemindersOnRequest
{
    public function __construct(
        private OpportunisticDateReminderDispatcher $dispatcher
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $this->dispatcher->dispatchIfDue($request->user());
    }
}

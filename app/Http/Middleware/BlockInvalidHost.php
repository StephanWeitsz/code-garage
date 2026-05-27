<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class BlockInvalidHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('analytics.block_invalid_hosts', true)) {
            return $next($request);
        }

        $host = Str::lower($request->getHost());

        if ($this->isAllowedHost($host)) {
            return $next($request);
        }

        Log::warning('Blocked request with invalid host header.', [
            'host' => $host,
            'ip' => $request->ip(),
            'path' => $request->path(),
            'query' => $request->query(),
            'user_agent' => $request->userAgent(),
        ]);

        abort(421, 'Misdirected Request');
    }

    private function isAllowedHost(string $host): bool
    {
        if (app()->environment('local') && in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }

        foreach (config('analytics.allowed_hosts', []) as $allowedHost) {
            if ($host === Str::lower($allowedHost)) {
                return true;
            }
        }

        return false;
    }
}

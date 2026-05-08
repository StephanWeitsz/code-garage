<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeploymentToolsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('deployment_tools.enabled'), 404);

        return $next($request);
    }
}


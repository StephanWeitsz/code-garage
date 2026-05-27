<?php

namespace App\Http\Middleware;

use App\Jobs\TrackPageVisitJob;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Support\Analytics\RiskClassifier;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        if ($this->shouldTrack($request)) {
            TrackPageVisitJob::dispatch($this->payload($request, $startedAt))->afterResponse();
        }

        return $response;
    }

    private function shouldTrack(Request $request): bool
    {
        if (! config('analytics.enabled', true)) {
            return false;
        }

        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        foreach (config('analytics.ignored_paths', []) as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        $routeName = $request->route()?->getName();

        if ($routeName) {
            foreach (config('analytics.ignored_route_names', []) as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function payload(Request $request, float $startedAt): array
    {
        return [
            'session_id' => $request->session()->getId(),
            'user_id' => $request->user()?->getKey(),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'referrer' => Str::limit((string) $request->headers->get('referer'), 1000, ''),
            'url' => Str::limit($request->fullUrlWithoutQuery(['token', 'signature', 'password', 'password_confirmation']), 2000, ''),
            'route_name' => $request->route()?->getName(),
            'page_title' => null,
            'method' => $request->method(),
            'response_time' => (int) round((microtime(true) - $startedAt) * 1000),
            'course' => $this->coursePayload($request),
        ] + app(RiskClassifier::class)->classify($request);
    }

    private function coursePayload(Request $request): ?array
    {
        if (! $this->isCourseRoute($request)) {
            return null;
        }

        $course = $request->route('course')
            ?? $request->route('slug')
            ?? $request->route('id');

        if (is_object($course)) {
            return [
                'id' => $course->getKey(),
                'slug' => $course->slug ?? null,
                'title' => $course->title ?? $course->name ?? null,
                'event_type' => 'view',
            ];
        }

        if (is_scalar($course)) {
            return [
                'slug' => (string) $course,
                'event_type' => 'view',
            ];
        }

        return null;
    }

    private function isCourseRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        foreach (config('analytics.course_route_patterns', []) as $pattern) {
            if ($routeName && Str::is($pattern, $routeName)) {
                return true;
            }
        }

        foreach (config('analytics.course_url_patterns', []) as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}

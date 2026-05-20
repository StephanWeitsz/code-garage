# Code Garage Visitor Analytics

This module adds lightweight, first-party visitor analytics for a Laravel and Livewire application.

## Install

Install the device detector:

```bash
composer require jenssegers/agent
```

Run migrations:

```bash
php artisan migrate
```

Register the middleware on the web middleware group.

Laravel 11/12 `bootstrap/app.php`:

```php
use App\Http\Middleware\TrackVisitor;

->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        TrackVisitor::class,
    ]);
})
```

Laravel 10 and earlier `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // existing middleware...
        \App\Http\Middleware\TrackVisitor::class,
    ],
];
```

Register `App\Providers\AnalyticsServiceProvider::class` if your Laravel version does not auto-discover application providers. In Laravel 11/12 this is usually `bootstrap/providers.php`; in older versions use `config/app.php`.

## Queue

Analytics writes are queued and dispatched after the HTTP response:

```bash
php artisan queue:work --queue=analytics,default
```

For production, run the queue worker under Supervisor/systemd. If `QUEUE_CONNECTION=sync`, writes still happen after the response callback but will not be processed by a separate worker.

## Dashboard

Routes are loaded from `routes/analytics.php`.

Default dashboard URL:

```text
/admin/analytics
```

Change `ANALYTICS_ADMIN_PREFIX` to move it.

The dashboard includes:

- stat cards for visitors, active users, registrations, page views, and anonymous visitors
- daily visitor and page-view charts
- course popularity, device, and browser charts
- recent visitor and most visited page tables
- live activity feed with Livewire polling
- visitor journey details

## Course Events

Course page views are inferred from configured course route or URL patterns. For funnel steps, post to:

```text
POST /analytics/course-event
```

Payload:

```json
{
  "course_id": 1,
  "course_slug": "python-basics",
  "course_title": "Python Basics",
  "event_type": "enroll_click"
}
```

Supported event types:

- `enroll_click`
- `registration_conversion`

## Privacy

The middleware tracks only GET page visits. It does not store request bodies, passwords, tokens, cookies, headers other than user agent/referrer, or POST payloads. Sensitive routes are excluded in `config/analytics.php`.

URLs are stored with common sensitive query keys removed:

- `token`
- `signature`
- `password`
- `password_confirmation`

Add any application-specific private paths or route names to `ignored_paths` and `ignored_route_names`.

## Retention

Configure retention:

```env
ANALYTICS_RETENTION_DAYS=180
```

Run cleanup manually:

```bash
php artisan analytics:prune
```

Schedule cleanup in `routes/console.php` or your console kernel:

```php
Schedule::command('analytics:prune')->daily();
```

## Testing Strategy

Recommended coverage:

- middleware ignores non-GET, auth, password, Livewire, admin auth, and asset routes
- middleware dispatches `TrackPageVisitJob` after response for normal GET pages
- job creates or updates visitor sessions and creates page visits
- authenticated requests attach `user_id`
- course routes create `course_views`
- `AnalyticsService` aggregates expected dashboard values from seeded analytics records
- prune command removes old visits and orphaned sessions

Use `Queue::fake()` for middleware tests and direct job execution for persistence tests.

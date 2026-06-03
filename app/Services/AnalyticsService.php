<?php

namespace App\Services;

use App\Models\CourseView;
use App\Models\PageVisit;
use App\Models\User;
use App\Models\VisitorSession;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function dashboardStats(): array
    {
        return Cache::remember('analytics.dashboard.stats', $this->cacheTtl(), fn () => [
            'visitors_today' => $this->totalVisitorsToday(),
            'active_visitors' => $this->activeVisitors(),
            'registered_users_today' => $this->registrationsToday(),
            'total_page_views' => PageVisit::query()->count(),
            'anonymous_visitors' => VisitorSession::query()->whereNull('user_id')->count(),
            'high_risk_today' => PageVisit::query()
                ->where('is_suspicious', true)
                ->whereDate('visited_at', today())
                ->count(),
        ]);
    }

    public function totalVisitorsToday(): int
    {
        return VisitorSession::query()
            ->whereDate('first_seen_at', today())
            ->count();
    }

    public function visitorsThisWeek(): int
    {
        return VisitorSession::query()
            ->where('first_seen_at', '>=', now()->startOfWeek())
            ->count();
    }

    public function activeVisitors(): int
    {
        return VisitorSession::query()->active()->count();
    }

    public function anonymousVsLoggedIn(): array
    {
        return [
            'anonymous' => VisitorSession::query()->whereNull('user_id')->count(),
            'logged_in' => VisitorSession::query()->whereNotNull('user_id')->count(),
        ];
    }

    public function registrationsToday(): int
    {
        return User::query()->whereDate('created_at', today())->count();
    }

    public function mostVisitedPages(int $limit = 10): Collection
    {
        return PageVisit::query()
            ->select('url', DB::raw('count(*) as visits'), DB::raw('count(distinct visitor_session_id) as unique_visitors'))
            ->where('is_suspicious', false)
            ->groupBy('url')
            ->orderByDesc('visits')
            ->limit($limit)
            ->get();
    }

    public function highRiskVisits(int $limit = 15): Collection
    {
        return PageVisit::query()
            ->with(['visitorSession.user:id,name,email'])
            ->where('is_suspicious', true)
            ->latest('visited_at')
            ->limit($limit)
            ->get();
    }

    public function riskyHosts(int $limit = 10): Collection
    {
        return PageVisit::query()
            ->select('request_host', DB::raw('count(*) as attempts'), DB::raw('count(distinct visitor_session_id) as unique_visitors'))
            ->where('is_suspicious', true)
            ->whereNotNull('request_host')
            ->groupBy('request_host')
            ->orderByDesc('attempts')
            ->limit($limit)
            ->get();
    }

    public function mostViewedCourses(int $limit = 10, ?string $range = null): Collection
    {
        $query = CourseView::query()
            ->select(
                'course_id',
                'course_slug',
                DB::raw('max(course_title) as course_title'),
                DB::raw("sum(case when event_type = 'view' then 1 else 0 end) as views"),
                DB::raw("sum(case when event_type = 'enroll_click' then 1 else 0 end) as enroll_clicks"),
                DB::raw("sum(case when event_type = 'registration_conversion' then 1 else 0 end) as conversions"),
            );

        $this->applyDateRange($query, 'occurred_at', $range);

        return $query
            ->groupBy('course_id', 'course_slug')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    public function recentVisitors(int $limit = 20): Collection
    {
        return VisitorSession::query()
            ->with('user:id,name,email')
            ->with(['pageVisits' => fn ($query) => $query->latest('visited_at')->limit(1)])
            ->latest('last_seen_at')
            ->limit($limit)
            ->get();
    }

    public function visitorJourney(int $visitorSessionId): Collection
    {
        return PageVisit::query()
            ->where('visitor_session_id', $visitorSessionId)
            ->orderBy('visited_at')
            ->get(['id', 'url', 'route_name', 'page_title', 'visited_at']);
    }

    public function popularReferrers(int $limit = 10): Collection
    {
        return VisitorSession::query()
            ->select('referrer', DB::raw('count(*) as visitors'))
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->groupBy('referrer')
            ->orderByDesc('visitors')
            ->limit($limit)
            ->get();
    }

    public function dailyVisitors(int $days = 14): array
    {
        return $this->dailySeries(
            VisitorSession::query(),
            'first_seen_at',
            $days,
            'count(*)'
        );
    }

    public function pageViewsOverTime(int $days = 14): array
    {
        return $this->dailySeries(
            PageVisit::query(),
            'visited_at',
            $days,
            'count(*)'
        );
    }

    public function breakdown(string $column, ?string $range = null): array
    {
        $query = VisitorSession::query()
            ->select($column, DB::raw('count(*) as total'))
            ->whereNotNull($column);

        $this->applyDateRange($query, 'first_seen_at', $range);

        return $query
            ->groupBy($column)
            ->orderByDesc('total')
            ->orderBy($column)
            ->limit(8)
            ->pluck('total', $column)
            ->toArray();
    }

    public function liveActivityFeed(int $limit = 15): Collection
    {
        return PageVisit::query()
            ->with(['visitorSession.user:id,name,email'])
            ->where('is_suspicious', false)
            ->latest('visited_at')
            ->limit($limit)
            ->get();
    }

    private function dailySeries($query, string $column, int $days, string $aggregate): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = $query
            ->selectRaw('date('.$column.') as day, '.$aggregate.' as total')
            ->where($column, '>=', $start)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        return collect(range(0, $days - 1))
            ->mapWithKeys(function (int $offset) use ($rows, $start): array {
                $day = Carbon::parse($start)->addDays($offset)->toDateString();

                return [$day => (int) ($rows[$day] ?? 0)];
            })
            ->toArray();
    }

    private function applyDateRange($query, string $column, ?string $range): void
    {
        match ($range) {
            'today' => $query->whereBetween($column, [now()->startOfDay(), now()->endOfDay()]),
            'week' => $query->whereBetween($column, [now()->subDays(6)->startOfDay(), now()->endOfDay()]),
            'month' => $query->whereBetween($column, [now()->startOfMonth(), now()->endOfDay()]),
            'year' => $query->whereBetween($column, [now()->startOfYear(), now()->endOfDay()]),
            default => null,
        };
    }

    private function cacheTtl(): int
    {
        return (int) config('analytics.cache_ttl_seconds', 60);
    }
}

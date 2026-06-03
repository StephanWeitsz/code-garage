<?php

namespace App\Livewire\Admin;

use App\Services\AnalyticsService;
use Livewire\Component;

class AnalyticsDashboard extends Component
{
    public array $stats = [];

    public array $dailyVisitors = [];

    public array $pageViews = [];

    public array $topCourses = [];

    public array $deviceBreakdown = [];

    public array $browserBreakdown = [];

    public string $chartRange = 'today';

    public array $chartRangeOptions = [
        'today' => 'Today',
        'week' => 'Week',
        'month' => 'Month',
        'year' => 'Year',
        'all' => 'All records',
    ];

    public function mount(AnalyticsService $analytics): void
    {
        $this->loadAnalytics($analytics);
    }

    public function refreshDashboard(AnalyticsService $analytics): void
    {
        $this->loadAnalytics($analytics);
    }

    public function setChartRange(string $range, AnalyticsService $analytics): void
    {
        $this->chartRange = array_key_exists($range, $this->chartRangeOptions) ? $range : 'today';

        $this->loadAnalytics($analytics);
    }

    public function render(AnalyticsService $analytics)
    {
        return view('livewire.admin.analytics-dashboard', [
            'recentVisitors' => $analytics->recentVisitors(),
            'mostVisitedPages' => $analytics->mostVisitedPages(),
            'activityFeed' => $analytics->liveActivityFeed(),
            'highRiskVisits' => $analytics->highRiskVisits(),
            'riskyHosts' => $analytics->riskyHosts(),
        ])->layout('layouts.app');
    }

    private function loadAnalytics(AnalyticsService $analytics): void
    {
        $this->stats = $analytics->dashboardStats();
        $this->dailyVisitors = $analytics->dailyVisitors();
        $this->pageViews = $analytics->pageViewsOverTime();
        $this->topCourses = $analytics->mostViewedCourses(range: $this->chartRange)->toArray();
        $this->deviceBreakdown = $analytics->breakdown('device_type', $this->chartRange);
        $this->browserBreakdown = $analytics->breakdown('browser', $this->chartRange);

        $this->dispatch('analytics-updated', charts: $this->chartPayload());
    }

    private function chartPayload(): array
    {
        return [
            'dailyVisitors' => [
                'labels' => array_keys($this->dailyVisitors),
                'data' => array_values($this->dailyVisitors),
            ],
            'pageViews' => [
                'labels' => array_keys($this->pageViews),
                'data' => array_values($this->pageViews),
            ],
            'topCourses' => [
                'labels' => collect($this->topCourses)
                    ->map(fn (array $course) => $course['course_title'] ?: $course['course_slug'] ?: 'Unknown course')
                    ->values()
                    ->all(),
                'data' => collect($this->topCourses)->pluck('views')->map(fn ($views) => (int) $views)->values()->all(),
            ],
            'devices' => [
                'labels' => array_keys($this->deviceBreakdown),
                'data' => array_values($this->deviceBreakdown),
            ],
            'browsers' => [
                'labels' => array_keys($this->browserBreakdown),
                'data' => array_values($this->browserBreakdown),
            ],
        ];
    }
}

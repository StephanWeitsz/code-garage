<?php

namespace Tests\Feature\Analytics;

use App\Livewire\Admin\AnalyticsDashboard;
use App\Models\CourseView;
use App\Models\VisitorSession;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class AnalyticsChartRangeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_chart_ranges_filter_course_views(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-03 12:00:00'));

        $today = $this->visitorSession('today', now());
        $withinWeek = $this->visitorSession('within-week', now()->subDays(6));
        $outsideWeek = $this->visitorSession('outside-week', now()->subDays(8));
        $previousYear = $this->visitorSession('previous-year', now()->subYear());

        $this->courseView($today, 'Laravel Basics', now());
        $this->courseView($withinWeek, 'Laravel Basics', now()->subDays(6));
        $this->courseView($outsideWeek, 'Laravel Basics', now()->subDays(8));
        $this->courseView($previousYear, 'Laravel Basics', now()->subYear());

        $analytics = app(AnalyticsService::class);

        $this->assertSame(1, (int) $analytics->mostViewedCourses(range: 'today')->first()->views);
        $this->assertSame(2, (int) $analytics->mostViewedCourses(range: 'week')->first()->views);
        $this->assertSame(3, (int) $analytics->mostViewedCourses(range: 'year')->first()->views);
        $this->assertSame(4, (int) $analytics->mostViewedCourses(range: 'all')->first()->views);
    }

    public function test_chart_ranges_filter_device_and_browser_breakdowns(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-03 12:00:00'));

        $this->visitorSession('today-desktop-chrome', now(), 'desktop', 'Chrome');
        $this->visitorSession('week-mobile-safari', now()->subDays(6), 'mobile', 'Safari');
        $this->visitorSession('old-tablet-firefox', now()->subDays(40), 'tablet', 'Firefox');

        $analytics = app(AnalyticsService::class);

        $this->assertSame(['desktop' => 1], $analytics->breakdown('device_type', 'today'));
        $this->assertSame(['desktop' => 1, 'mobile' => 1], $analytics->breakdown('device_type', 'week'));
        $this->assertSame(['Chrome' => 1, 'Firefox' => 1, 'Safari' => 1], $analytics->breakdown('browser', 'all'));
    }

    public function test_dashboard_defaults_to_today_and_changes_chart_range(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-03 12:00:00'));

        Livewire::test(AnalyticsDashboard::class)
            ->assertSet('chartRange', 'today')
            ->call('setChartRange', 'week')
            ->assertSet('chartRange', 'week');
    }

    private function visitorSession(string $sessionId, Carbon $seenAt, string $deviceType = 'desktop', string $browser = 'Chrome'): VisitorSession
    {
        return VisitorSession::query()->create([
            'session_id' => $sessionId,
            'device_type' => $deviceType,
            'browser' => $browser,
            'first_seen_at' => $seenAt,
            'last_seen_at' => $seenAt,
        ]);
    }

    private function courseView(VisitorSession $visitorSession, string $courseTitle, Carbon $occurredAt): void
    {
        CourseView::query()->create([
            'visitor_session_id' => $visitorSession->id,
            'course_slug' => str($courseTitle)->slug()->toString(),
            'course_title' => $courseTitle,
            'event_type' => CourseView::EVENT_VIEW,
            'occurred_at' => $occurredAt,
        ]);
    }
}
